# Run organisation data exports in the requested tenant context

**Type:** Bug

## Problem

The organisation data privacy routes in `routes/web.php` are registered outside the `tenant` middleware group. This leaves the export request and download without a current tenant.

On export, `ExportOrganisationDataJob` is dispatched without tenant context. `DataExportService` later creates an `OrganisationDataExport`, whose `BelongsToOrganisation` creation hook throws when no tenant is current. On download, implicit binding for the scoped `OrganisationDataExport` model applies its fail-closed global scope and cannot find the export.

The tenancy coverage in `tests/Feature/Tenancy/TenantHttpIsolationTest.php` explicitly opts this model out because it has no test, and the export flow currently has no feature coverage.

## Files to change

- `routes/web.php`: ensure data privacy export actions establish tenant context for the organisation named in the route.
- `app/Http/Controllers/OrganisationController.php`: ensure export dispatch and download resolve the export within the authorised organisation.
- `app/Services/Organisations/DataExportService.php` and `app/Jobs/ExportOrganisationDataJob.php`: preserve the requested organisation context through job execution and export-record creation.
- Add feature coverage under `tests/Feature/Organisations/` for export requests, downloads, and cross-organisation access.
- `tests/Feature/Tenancy/TenantHttpIsolationTest.php`: replace the export model's coverage opt-out with coverage for its download route.

## Change to make

Establish the route's authorised `Organisation` as the tenant before dispatching the export job and before resolving the scoped export record. Ensure the queued job carries that same organisation context; do not rely on whichever organisation happens to be selected in the user's session.

## Acceptance criteria

- An authorised organisation owner can request an export and the job completes with an `OrganisationDataExport` record.
- The owner can download the completed archive for that organisation.
- An export belonging to another organisation cannot be downloaded, even when its ID is supplied in the URL.
- Tests verify the job and download use the organisation from the route, including when the user belongs to multiple organisations.
