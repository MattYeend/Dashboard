<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CustomDashboardWidgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $label
 * @property string $metric_key
 * @property string $date_range
 * @property int $position
 * @property bool $is_visible
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'label',
    'metric_key',
    'date_range',
    'position',
    'is_visible',
])]
class CustomDashboardWidget extends Model
{
    /** @use HasFactory<CustomDashboardWidgetFactory> */
    use HasFactory;

    /**
     * Get the user this custom widget belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
