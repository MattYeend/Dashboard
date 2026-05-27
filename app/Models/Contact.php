<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $contactable_type
 * @property int $contactable_id
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postal_code
 * @property string|null $country
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Model $contactable
 */
#[Fillable([
    'contactable_type',
    'contactable_id',
    'phone',
    'email',
    'address',
    'city',
    'postal_code',
    'country',
])]
class Contact extends Model
{
    /**
     * @use HasFactory<ContactFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the parent contactable model (e.g. User, Lead, Customer).
     *
     * @return MorphTo<Model,$this>
     */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }
}
