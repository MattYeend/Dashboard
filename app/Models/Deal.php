<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int|null $pipeline_id
 * @property int|null $stage_id
 * @property int|null $status_id
 * @property int|null $company_id
 * @property int|null $invoice_id
 * @property int $value
 * @property string $currency
 * @property int $probability
 * @property Carbon|null $expected_close_date
 * @property Carbon|null $closed_at
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Pipeline|null $pipeline
 * @property-read PipelineStage|null $stage
 * @property-read DealStatus|null $status
 * @property-read Company|null $company
 * @property-read Invoice|null $invoice
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'title',
    'description',
    'pipeline_id',
    'stage_id',
    'status_id',
    'company_id',
    'invoice_id',
    'value',
    'currency',
    'probability',
    'expected_close_date',
    'closed_at',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'deleted_at',
    'restored_by',
    'restored_at',
])]
class Deal extends Model implements Auditable
{
    /**
     * @use HasFactory<DealFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the pipeline this deal belongs to.
     *
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the pipeline stage this deal currently sits in.
     *
     * @return BelongsTo<PipelineStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    /**
     * Get the status of this deal.
     *
     * @return BelongsTo<DealStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(DealStatus::class, 'status_id');
    }

    /**
     * Get the company this deal belongs to.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the invoice raised from this deal.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who created this deal.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this deal.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this deal.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this deal.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the deal's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'title',
            'description',
            'pipeline_id',
            'stage_id',
            'status_id',
            'company_id',
            'invoice_id',
            'value',
            'currency',
            'probability',
            'expected_close_date',
            'closed_at',
            'meta',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'closed_at' => 'date',
            'meta' => 'array',
            'deleted_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }
}
