<?php

namespace App\Services\Companies;

use App\Models\Company;

class FormatterService
{
    /**
     * Format a single company with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'email' => $company->email,
            'phone' => $company->phone,
            'website' => $company->website,
            'registration_number' => $company->registration_number,
            'vat_number' => $company->vat_number,
            'description' => $company->description,
            'industry_id' => $company->industry_id,
            'account_manager_id' => $company->account_manager_id,
            'employee_count' => $company->employee_count,
            'founded_year' => $company->founded_year,
            'meta' => $company->meta,
            'created_at' => $company->created_at,
            'updated_at' => $company->updated_at,
            'deleted_at' => $company->deleted_at,
            'restored_at' => $company->restored_at,
            'industry' => $company->industry ? ['id' => $company->industry->id, 'title' => $company->industry->title] : null,
            'account_manager' => $company->accountManager ? ['id' => $company->accountManager->id, 'name' => $company->accountManager->name] : null,
            'creator' => $company->creator ? ['id' => $company->creator->id, 'name' => $company->creator->name] : null,
            'updater' => $company->updater ? ['id' => $company->updater->id, 'name' => $company->updater->name] : null,
            'deleter' => $company->deleter ? ['id' => $company->deleter->id, 'name' => $company->deleter->name] : null,
            'restorer' => $company->restorer ? ['id' => $company->restorer->id, 'name' => $company->restorer->name] : null,
        ];
    }
}
