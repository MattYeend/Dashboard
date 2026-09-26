<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Database\Factories\OrganisationDataExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A record of a generated Organisation data export archive.
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $requested_by
 * @property string $disk_path
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'requested_by',
    'disk_path',
    'completed_at',
])]
class OrganisationDataExport extends Model
{
    /**
     * @use HasFactory<OrganisationDataExportFactory>
     */
    use BelongsToOrganisation,
        HasFactory;

    /**
     * Get the user who requested this export.
     *
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
