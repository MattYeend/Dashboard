<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    // Login/Logout
    public const ACTION_LOGIN = 1;
    public const ACTION_LOGOUT = 2;
    public const ACTION_LOGIN_FAILED = 3;
    public const ACTION_LOGIN_PASSWORD_FAILED = 4;
    public const ACTION_LOGIN_EMAIL_FAILED = 5;
    public const ACTION_LOGIN_USERNAME_FAILED = 6;
    public const ACTION_LOGIN_SUCCESS = 7;

    // User Management
    public const ACTION_CREATE_USER = 8;
    public const ACTION_UPDATE_USER = 9;
    public const ACTION_DELETE_USER = 10;
    public const ACTION_SHOW_USER = 11;
    public const ACTION_WELCOME_EMAIL_SENT = 12;
    public const ACTION_CONFIRM_PASSWORD = 13;
    public const ACTION_FORGOT_PASSWORD = 14;
    public const ACTION_REGISTER_USER = 15;
    public const ACTION_RESET_PASSWORD = 16;
    public const ACTION_RESET_EMAIL = 17;
    public const ACTION_RESET_USERNAME = 18;
    public const ACTION_VERIFY_USER = 19;
    public const ACTION_PASSWORD_CHANGED = 20;
    public const ACTION_USER_RESTORED = 21;
    public const ACTION_USER_DELETED = 22;
    public const ACTION_USER_FORCE_DELETED = 23;

    // MFA/Settings
    public const ACTION_MFA_ENABLED = 24;
    public const ACTION_MFA_DISABLED = 25;
    public const ACTION_PROFILE_UPDATED = 26;
    public const ACTION_PROFILE_DELETED = 27;
    public const ACTION_EMAIL_UPDATED = 28;

    // Role/Permission Management
    public const ACTION_ROLE_ASSIGNED = 29;
    public const ACTION_PERMISSION_GRANTED = 30;
    public const ACTION_PERMISSION_REVOKED = 31;

    // Errors/Cache
    public const ACTION_GENERAL_ERROR = 32;
    public const ACTION_FOUR_HUNDRED_ERROR = 33;
    public const ACTION_FIVE_HUNDRED_ERRORS = 34;
    public const ACTION_CLEAR_CACHE = 35;

    // New Logging Actions should go here to be reviewed
    // by the development team for future releases.
    // Ensure to update the documentation accordingly.

    // Empty constants
    public const ACTION_NONE = 000;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'action_id',
        'data',
        'logged_in_user_id',
        'related_to_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<User,Log>
     */
    public function loggedInUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_in_user_id');
    }

    /**
     * Get the user related to the action, if applicable.
     *
     * @return BelongsTo<User,Log>
     */
    public function relatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_to_user_id');
    }

    /**
     * Log an action.
     *
     * @param int $action
     * @param array|null $data
     * @param int|null $logged_in_user_id
     * @param int|null $related_to_user_id
     */
    public static function log(
        $action = 0,
        $data = null,
        $logged_in_user_id = null,
        $related_to_user_id = null
    ) {
        if (isset($action)) {
            $logged_in_user_id = $logged_in_user_id ?? Auth::id();

            if (! is_null($data) && ! is_array($data)) {
                throw new \InvalidArgumentException(
                    'Data must be an array or null.'
                );
            }

            $log = new self();
            $log->logged_in_user_id = $logged_in_user_id;
            $log->action_id = $action;
            $log->related_to_user_id = $related_to_user_id;
            $log->data = $data;
            $log->save();
        }
    }

    /**
     * Scope a query to only include logs of a given action type.
     *
     * @param  Builder<Log> $query The query builder instance.
     * @param  int $action The action constant to filter by.
     *
     * @return Builder<Log> The modified query builder instance.
     */
    public function scopeOfAction(Builder $query, int $action): Builder
    {
        return $query->where('action_id', $action);
    }
}
