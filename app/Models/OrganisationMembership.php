<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganisationMembership extends Pivot
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
