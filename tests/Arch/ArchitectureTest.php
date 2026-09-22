<?php

use App\Contracts\Auditable;
use App\Services\EscapesLikeValues;
use Illuminate\Foundation\Http\FormRequest;

arch()->preset()->php();

arch()->preset()->security();

arch('audited models implement Auditable')
    ->expect('App\Models')
    ->toImplement(Auditable::class)
    ->ignoring([
        'App\Models\Concerns',
        'App\Models\Scopes',
        'App\Models\Log',
        'App\Models\Permission',
        'App\Models\OrganisationMembership',
        'App\Models\Subscription',
        'App\Models\CustomDashboardWidget',
        'App\Models\DashboardWidgetPreference',
        'App\Models\Like',
        'App\Models\OrganisationDataExport',
    ]);

arch('services use the Service suffix')
    ->expect('App\Services')
    ->toHaveSuffix('Service')
    ->ignoring([
        EscapesLikeValues::class,
        'App\Services\Concerns',
    ]);

arch('controllers use the Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend(FormRequest::class);

arch('policies use the Policy suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('controllers never use the DB facade directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

arch('models do not depend on the HTTP layer')
    ->expect('App\Models')
    ->not->toUse('App\Http');
