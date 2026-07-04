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

    public const ACTION_VIEW_USER = 11;

    public const ACTION_WELCOME_EMAIL_SENT = 12;

    public const ACTION_CONFIRM_PASSWORD = 13;

    public const ACTION_FORGOT_PASSWORD = 14;

    public const ACTION_REGISTER_USER = 15;

    public const ACTION_RESET_PASSWORD = 16;

    public const ACTION_RESET_EMAIL = 17;

    public const ACTION_RESET_USERNAME = 18;

    public const ACTION_VERIFY_USER = 19;

    public const ACTION_PASSWORD_CHANGED = 20;

    public const ACTION_RESTORE_USER = 21;

    public const ACTION_USER_DELETED = 22;

    public const ACTION_FORCE_DELETE_USER = 23;

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

    // Contact Management
    public const ACTION_CREATE_CONTACT = 36;

    public const ACTION_UPDATE_CONTACT = 37;

    public const ACTION_VIEW_CONTACT = 38;

    public const ACTION_DELETE_CONTACT = 39;

    public const ACTION_FORCE_DELETE_CONTACT = 40;

    public const ACTION_ASSIGN_CONTACT = 41;

    public const ACTION_UNASSIGN_CONTACT = 42;

    public const ACTION_RESTORE_CONTACT = 43;

    public const ACTION_IMPORT_CONTACT = 44;

    public const ACTION_EXPORT_CONTACT = 45;

    public const ACTION_CONTACT_UPDATED_BY_CRON = 46;

    // Task Status Management
    public const ACTION_CREATE_TASK_STATUS = 47;

    public const ACTION_UPDATE_TASK_STATUS = 48;

    public const ACTION_VIEW_TASK_STATUS = 49;

    public const ACTION_DELETE_TASK_STATUS = 50;

    public const ACTION_FORCE_DELETE_TASK_STATUS = 51;

    public const ACTION_RESTORE_TASK_STATUS = 52;

    public const ACTION_IMPORT_TASK_STATUS = 53;

    public const ACTION_EXPORT_TASK_STATUS = 54;

    public const ACTION_TASK_STATUS_UPDATED_BY_CRON = 55;

    public const ACTION_ASSIGN_TASK_STATUS = 56;

    public const ACTION_UNASSIGN_TASK_STATUS = 57;

    // Task Management
    public const ACTION_CREATE_TASK = 58;

    public const ACTION_UPDATE_TASK = 59;

    public const ACTION_VIEW_TASK = 60;

    public const ACTION_DELETE_TASK = 61;

    public const ACTION_FORCE_DELETE_TASK = 62;

    public const ACTION_RESTORE_TASK = 63;

    public const ACTION_IMPORT_TASK = 64;

    public const ACTION_EXPORT_TASK = 65;

    public const ACTION_TASK_UPDATED_BY_CRON = 66;

    public const ACTION_ASSIGN_TASK = 67;

    public const ACTION_UNASSIGN_TASK = 68;

    public const ACTION_CHANGE_TASK_STATUS = 69;

    public const ACTION_COMMENT_TASK = 70;

    public const ACTION_DELETE_TASK_COMMENT = 71;

    public const ACTION_EDIT_TASK_COMMENT = 72;

    public const ACTION_VIEW_TASK_COMMENT = 73;

    public const ACTION_TASK_COMMENT_UPDATED_BY_CRON = 74;

    // Order Status Management
    public const ACTION_CREATE_ORDER_STATUS = 75;

    public const ACTION_UPDATE_ORDER_STATUS = 76;

    public const ACTION_VIEW_ORDER_STATUS = 77;

    public const ACTION_DELETE_ORDER_STATUS = 78;

    public const ACTION_FORCE_DELETE_ORDER_STATUS = 79;

    public const ACTION_RESTORE_ORDER_STATUS = 80;

    public const ACTION_IMPORT_ORDER_STATUS = 81;

    public const ACTION_EXPORT_ORDER_STATUS = 82;

    public const ACTION_ORDER_STATUS_UPDATED_BY_CRON = 83;

    // Order Management
    public const ACTION_CREATE_ORDER = 84;

    public const ACTION_UPDATE_ORDER = 85;

    public const ACTION_VIEW_ORDER = 86;

    public const ACTION_DELETE_ORDER = 87;

    public const ACTION_FORCE_DELETE_ORDER = 88;

    public const ACTION_RESTORE_ORDER = 89;

    public const ACTION_IMPORT_ORDER = 90;

    public const ACTION_EXPORT_ORDER = 91;

    public const ACTION_ORDER_UPDATED_BY_CRON = 92;

    public const ACTION_CHANGE_ORDER_STATUS = 93;

    // Industry Management
    public const ACTION_CREATE_INDUSTRY = 94;
    public const ACTION_UPDATE_INDUSTRY = 95;
    public const ACTION_VIEW_INDUSTRY = 96;
    public const ACTION_DELETE_INDUSTRY = 97;
    public const ACTION_FORCE_DELETE_INDUSTRY = 98;
    public const ACTION_RESTORE_INDUSTRY = 99;
    public const ACTION_IMPORT_INDUSTRY = 100;
    public const ACTION_EXPORT_INDUSTRY = 101;
    public const ACTION_INDUSTRY_UPDATED_BY_CRON = 102;
    public const ACTION_ASSIGN_INDUSTRY = 103;

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
     */
    public static function log(
        int $action = self::ACTION_NONE,
        ?array $data = null,
        ?int $loggedInUserId = null,
        ?int $relatedToUserId = null
    ): self {
        return self::create([
            'action_id' => $action,
            'data' => $data,
            'logged_in_user_id' => $loggedInUserId ?? Auth::id(),
            'related_to_user_id' => $relatedToUserId,
        ]);
    }

    /**
     * Scope a query to only include logs of a given action type.
     *
     * @param  Builder<Log>  $query  The query builder instance.
     * @param  int  $action  The action constant to filter by.
     * @return Builder<Log> The modified query builder instance.
     */
    public function scopeOfAction(Builder $query, int $action): Builder
    {
        return $query->where('action_id', $action);
    }
}
