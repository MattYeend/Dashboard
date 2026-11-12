<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionService
{
    /**
     * Allowed transitions.
     *
     * @var array<string, array{from: string, to: string, column: string, log: int}>
     */
    private const TRANSITIONS = [
        'send' => [
            'from' => QuoteStatus::KEY_DRAFT,
            'to' => QuoteStatus::KEY_SENT,
            'column' => 'sent_at',
            'log' => Log::ACTION_SEND_QUOTE,
        ],
        'accept' => [
            'from' => QuoteStatus::KEY_SENT,
            'to' => QuoteStatus::KEY_ACCEPTED,
            'column' => 'accepted_at',
            'log' => Log::ACTION_ACCEPT_QUOTE,
        ],
        'decline' => [
            'from' => QuoteStatus::KEY_SENT,
            'to' => QuoteStatus::KEY_DECLINED,
            'column' => 'declined_at',
            'log' => Log::ACTION_DECLINE_QUOTE,
        ],
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    public function send(Quote $quote, User $actor): Quote
    {
        return $this->apply('send', $quote, $actor);
    }

    public function accept(Quote $quote, User $actor): Quote
    {
        return $this->apply('accept', $quote, $actor);
    }

    public function decline(Quote $quote, User $actor): Quote
    {
        return $this->apply('decline', $quote, $actor);
    }

    /**
     * Expire every Sent quote whose valid_until has passed, for the
     * current tenant. Safe to run repeatedly.
     *
     * @return int Number of quotes expired
     */
    public function expireOverdue(?User $actor = null): int
    {
        $sentId = QuoteStatus::idForKey(QuoteStatus::KEY_SENT);
        $expiredId = QuoteStatus::idForKey(QuoteStatus::KEY_EXPIRED);
        $count = 0;

        Quote::query()
            ->where('status_id', $sentId)
            ->where('valid_until', '<', now())
            ->chunkById(100, function ($quotes) use ($sentId, $expiredId, $actor, &$count): void {
                foreach ($quotes as $quote) {
                    DB::transaction(function () use ($quote, $sentId, $expiredId, $actor, &$count): void {
                        $locked = Quote::query()
                            ->whereKey($quote->id)
                            ->where('status_id', $sentId)
                            ->lockForUpdate()
                            ->first();

                        if ($locked === null) {
                            return;
                        }

                        $before = $this->auditLogService->snapshot($locked);

                        $locked->forceFill([
                            'status_id' => $expiredId,
                            'expired_at' => now(),
                        ])->save();

                        $this->auditLogService->record(
                            Log::ACTION_EXPIRE_QUOTE,
                            $actor,
                            $locked,
                            [
                                'before' => $before,
                                'after' => $this->auditLogService->snapshot($locked),
                            ],
                        );

                        $count++;
                    });
                }
            });

        return $count;
    }

    /**
     * Apply a named transition under a row lock.
     *
     * @throws ValidationException
     */
    private function apply(string $transition, Quote $quote, User $actor): Quote
    {
        $config = self::TRANSITIONS[$transition];

        return DB::transaction(function () use ($config, $transition, $quote, $actor): Quote {
            $locked = Quote::query()
                ->with('status')
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isStatus($config['from'])) {
                throw ValidationException::withMessages([
                    'status' => "This quote cannot be moved to '{$config['to']}' from its current status.",
                ]);
            }

            $this->assertPreconditions($transition, $locked);

            $before = $this->auditLogService->snapshot($locked);

            $attributes = [
                'status_id' => QuoteStatus::idForKey($config['to']),
                $config['column'] => now(),
                'updated_by' => $actor->id,
            ];

            if ($transition === 'send' && $locked->issued_at === null) {
                $attributes['issued_at'] = now();
            }

            $locked->forceFill($attributes)->save();
            $locked->unsetRelation('status');

            $this->auditLogService->record(
                $config['log'],
                $actor,
                $locked,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($locked),
                ],
            );

            return $locked;
        });
    }

    /**
     * Business rules that must hold before a transition.
     *
     * @throws ValidationException
     */
    private function assertPreconditions(string $transition, Quote $quote): void
    {
        if ($transition === 'send') {
            if ($quote->valid_until === null || $quote->valid_until->isPast()) {
                throw ValidationException::withMessages([
                    'valid_until' => 'Set a valid until date in the future before sending.',
                ]);
            }

            if (bccomp((string) $quote->total_amount, '0', 2) !== 1) {
                throw ValidationException::withMessages([
                    'total_amount' => 'A quote must have a total greater than zero before it is sent.',
                ]);
            }
        }

        if (in_array($transition, ['accept', 'decline'], true)
            && $quote->valid_until !== null
            && $quote->valid_until->isPast()
        ) {
            throw ValidationException::withMessages([
                'valid_until' => 'This quote has passed its valid until date.',
            ]);
        }
    }
}
