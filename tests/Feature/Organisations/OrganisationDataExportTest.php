<?php

use App\Jobs\ExportOrganisationDataJob;
use App\Models\Organisation;
use App\Models\OrganisationDataExport;
use App\Notifications\OrganisationDataExportReady;
use App\Services\Organisations\DataExportService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsOrganisationMember;

uses(
    LazilyRefreshDatabase::class,
    ActsAsOrganisationMember::class
);

/**
 * Create a completed export with a real archive on the fake disk.
 */
function createCompletedExport(
    Organisation $organisation,
    int $requestedBy
): OrganisationDataExport {
    $path = 'organisation-exports/'.$organisation->id.'/test.zip';
    Storage::disk('local')->put($path, 'archive');

    return $organisation->execute(
        fn () => OrganisationDataExport::query()->create([
            'requested_by' => $requestedBy,
            'disk_path' => $path,
            'completed_at' => now(),
        ])
    );
}

/**
 * Count export records for an organisation, ignoring tenant scoping.
 */
function exportCountFor(Organisation $organisation): int
{
    return OrganisationDataExport::withoutGlobalScopes()
        ->where('organisation_id', $organisation->id)
        ->count();
}

beforeEach(function () {
    config(['queue.default' => 'sync']);
    Storage::fake('local');
    Notification::fake();
    $this->setUpOrganisationIsolation();
});

afterEach(function () {
    Organisation::forgetCurrent();
});

describe('requesting an export', function () {
    test('runs the export in the route organisation, not the session organisation', function () {
        $user = $this->memberOfMany([
            $this->organisationA,
            $this->organisationB,
        ]);

        $this
            ->actingAsMemberOf($this->organisationA, $user)
            ->post(route('organisations.data-privacy.export', $this->organisationB))
            ->assertRedirect();

        expect(exportCountFor($this->organisationB))->toBe(1)
            ->and(exportCountFor($this->organisationA))->toBe(0);

        Notification::assertSentTo($user, OrganisationDataExportReady::class);
    });

    test('does not dispatch an export for an organisation the user does not belong to', function () {
        $user = $this->memberOf($this->organisationA);

        Bus::fake();

        $this
            ->actingAsMemberOf($this->organisationA, $user)
            ->post(route('organisations.data-privacy.export', $this->organisationB))
            ->assertNotFound();

        Bus::assertNotDispatched(ExportOrganisationDataJob::class);
    });
});

describe('running the job', function () {
    test('creates the export for its organisation when no tenant is current', function () {
        $user = $this->memberOf($this->organisationA);
        Organisation::forgetCurrent();

        (new ExportOrganisationDataJob($this->organisationA, $user))
            ->handle(app(DataExportService::class));

        expect(exportCountFor($this->organisationA))->toBe(1)
            ->and(Organisation::current())->toBeNull();
    });

    test('creates the export for its organisation when another tenant is current', function () {
        $user = $this->memberOf($this->organisationA);
        $this->organisationB->makeCurrent();

        (new ExportOrganisationDataJob($this->organisationA, $user))
            ->handle(app(DataExportService::class));

        expect(exportCountFor($this->organisationA))->toBe(1)
            ->and(exportCountFor($this->organisationB))->toBe(0)
            ->and(Organisation::current()->getKey())
            ->toBe($this->organisationB->getKey());
    });
});

describe('downloading an export', function () {
    test('lets the owner download a completed export', function () {
        $user = $this->memberOf($this->organisationA);
        $export = createCompletedExport($this->organisationA, $user->id);

        $this
            ->actingAsMemberOf($this->organisationA, $user)
            ->get(route('organisations.data-privacy.download', [
                $this->organisationA,
                $export->getKey(),
            ]))
            ->assertOk();
    });

    test('uses the route organisation when the session organisation differs', function () {
        $user = $this->memberOfMany([
            $this->organisationA,
            $this->organisationB,
        ]);
        $export = createCompletedExport($this->organisationA, $user->id);

        $this
            ->actingAsMemberOf($this->organisationB, $user)
            ->get(route('organisations.data-privacy.download', [
                $this->organisationA,
                $export->getKey(),
            ]))
            ->assertOk();
    });

    test('returns 404 when the export belongs to another organisation', function () {
        $user = $this->memberOfMany([
            $this->organisationA,
            $this->organisationB,
        ]);
        $export = createCompletedExport($this->organisationA, $user->id);

        $this
            ->actingAsMemberOf($this->organisationA, $user)
            ->get(route('organisations.data-privacy.download', [
                $this->organisationB,
                $export->getKey(),
            ]))
            ->assertNotFound();
    });

    test('returns 404 for a user who does not belong to the route organisation', function () {
        $owner = $this->memberOf($this->organisationA);
        $export = createCompletedExport($this->organisationA, $owner->id);
        $userB = $this->memberOf($this->organisationB);

        $this
            ->actingAsMemberOf($this->organisationB, $userB)
            ->get(route('organisations.data-privacy.download', [
                $this->organisationA,
                $export->getKey(),
            ]))
            ->assertNotFound();
    });
});
