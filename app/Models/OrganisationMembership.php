<?php

namespace App\Models;

use App\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganisationMembership extends Pivot implements Auditable
{
    public const STATUS_INVITED = 'invited';

    public const STATUS_ACTIVE = 'active';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organisation_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get a snapshot of the membership's attributes for audit purposes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'organisation_id',
            'user_id',
            'status',
            'invited_at',
            'joined_at',
            'invited_by',
        ]);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }
}
