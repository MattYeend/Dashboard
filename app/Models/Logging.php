<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Logging extends Model
{
    // Action constants
    // Login/Logout
    public const ACTION_LOGIN = 1;
    public const ACTION_LOGOUT = 2;

    // User Management
    public const ACTION_CREATE_USER = 3;
    public const ACTION_UPDATE_USER = 4;
    public const ACTION_DELETE_USER = 5;
    public const ACTION_SHOW_USER = 6;
    public const ACTION_WELCOME_EMAIL_SENT = 7;
    public const ACTION_CONFIRM_PASSWORD = 8;
    public const ACTION_FORGOT_PASSWORD = 9;
    public const ACTION_REGISTER_USER = 10;
    public const ACTION_LOGIN_FAILED = 11;
    public const ACTION_LOGIN_PASSWORD_FAILED = 12;
    public const ACTION_LOGIN_EMAIL_FAILED = 13;
    public const ACTION_LOGIN_USERNAME_FAILED = 14;
    public const ACTION_LOGIN_SUCCESS = 15;
    public const ACTION_RESET_PASSWORD = 16;
    public const ACTION_RESET_EMAIL = 17;
    public const ACTION_RESET_USERNAME = 18;
    public const ACTION_VERIFY_USER = 19;
    public const ACTION_PASSWORD_CHANGED = 20;

    // MFA/Settings
    public const ACTION_MFA_ENABLED = 21;
    public const ACTION_MFA_DISABLED = 22;
    public const ACTION_PROFILE_UPDATED = 23;
    public const ACTION_PROFILE_DELETED = 24;
    public const ACTION_EMAIL_UPDATED = 25;

    // Role/Permission Management
    public const ACTION_ROLE_ASSIGNED = 26;
    public const ACTION_PERMISSION_GRANTED = 27;
    public const ACTION_PERMISSION_REVOKED = 28;

    // Errors/Cache
    public const ACTION_GENERAL_ERROR = 29;
    public const ACTION_FOUR_HUNDRED_ERROR = 30;
    public const ACTION_FIVE_HUNDRED_ERRORS = 31;
    public const ACTION_CLEAR_CACHE = 32;

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
    protected $table = 'loggings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loggedInUser()
    {
        return $this->belongsTo(User::class, 'logged_in_user_id');
    }

    /**
     * Get the user related to the action, if applicable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relatedToUser()
    {
        return $this->belongsTo(User::class, 'related_to_user_id');
    }

    /**
     * Log an action.
     *
     * @param int $action The action constant.
     * @param array|null $data Additional data related to the action.
     * @param int|null $logged_in_user_id The ID of the user performing
     * the action.
     * @param int|null $related_to_user_id The ID of the user related
     * to the action.
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
                throw new \InvalidArgumentException('Data must be an array or null.');
            }

            $log = new self();
            $log->logged_in_user_id = $logged_in_user_id;
            $log->action_id = $action;
            $log->related_to_user_id = $related_to_user_id;
            $log->data = $data;
            $log->save();
        }
    }
}
