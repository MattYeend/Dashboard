<?php

namespace App\Models;

use Database\Factories\DashboardWidgetPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property int $user_id
 * @property string $widget_key
 * @property int $position
 * @property bool $is_visible
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'widget_key',
    'position',
    'is_visible',
])]
class DashboardWidgetPreference extends Model
{
    /** @use HasFactory<DashboardWidgetPreferenceFactory> */
    use HasFactory;

    /**
     * Get the user this widget preference belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
