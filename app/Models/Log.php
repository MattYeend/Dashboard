<?php

namespace App\Models;

use App\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Log extends Model implements Auditable
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

    // Company Management
    public const ACTION_CREATE_COMPANY = 104;

    public const ACTION_UPDATE_COMPANY = 105;

    public const ACTION_VIEW_COMPANY = 106;

    public const ACTION_DELETE_COMPANY = 107;

    public const ACTION_FORCE_DELETE_COMPANY = 108;

    public const ACTION_RESTORE_COMPANY = 109;

    public const ACTION_IMPORT_COMPANY = 110;

    public const ACTION_EXPORT_COMPANY = 111;

    public const ACTION_COMPANY_UPDATED_BY_CRON = 112;

    // Plan Management
    public const ACTION_CREATE_PLAN = 113;

    public const ACTION_UPDATE_PLAN = 114;

    public const ACTION_VIEW_PLAN = 115;

    public const ACTION_DELETE_PLAN = 116;

    public const ACTION_FORCE_DELETE_PLAN = 117;

    public const ACTION_RESTORE_PLAN = 118;

    public const ACTION_IMPORT_PLAN = 119;

    public const ACTION_EXPORT_PLAN = 120;

    public const ACTION_PLAN_UPDATED_BY_CRON = 121;

    // Address Management
    public const ACTION_CREATE_ADDRESS = 122;

    public const ACTION_UPDATE_ADDRESS = 123;

    public const ACTION_VIEW_ADDRESS = 124;

    public const ACTION_DELETE_ADDRESS = 125;

    public const ACTION_FORCE_DELETE_ADDRESS = 126;

    public const ACTION_RESTORE_ADDRESS = 127;

    public const ACTION_IMPORT_ADDRESS = 128;

    public const ACTION_EXPORT_ADDRESS = 129;

    public const ACTION_ADDRESS_UPDATED_BY_CRON = 130;

    public const ACTION_ASSIGN_ADDRESS = 131;

    // Category Management
    public const ACTION_CREATE_CATEGORY = 132;

    public const ACTION_UPDATE_CATEGORY = 133;

    public const ACTION_VIEW_CATEGORY = 134;

    public const ACTION_DELETE_CATEGORY = 135;

    public const ACTION_FORCE_DELETE_CATEGORY = 136;

    public const ACTION_RESTORE_CATEGORY = 137;

    public const ACTION_IMPORT_CATEGORY = 138;

    public const ACTION_EXPORT_CATEGORY = 139;

    public const ACTION_CATEGORY_UPDATED_BY_CRON = 140;

    public const ACTION_ASSIGN_CATEGORY = 141;

    public const ACTION_ASSIGN_CATEGORY_TO_CATEGORY = 142;

    // Post Management
    public const ACTION_CREATE_POST = 143;

    public const ACTION_UPDATE_POST = 144;

    public const ACTION_VIEW_POST = 145;

    public const ACTION_DELETE_POST = 146;

    public const ACTION_FORCE_DELETE_POST = 147;

    public const ACTION_RESTORE_POST = 148;

    public const ACTION_IMPORT_POST = 149;

    public const ACTION_EXPORT_POST = 150;

    public const ACTION_POST_UPDATED_BY_CRON = 151;

    // Invoice Status Management
    public const ACTION_CREATE_INVOICE_STATUS = 152;

    public const ACTION_UPDATE_INVOICE_STATUS = 153;

    public const ACTION_VIEW_INVOICE_STATUS = 154;

    public const ACTION_DELETE_INVOICE_STATUS = 155;

    public const ACTION_FORCE_DELETE_INVOICE_STATUS = 156;

    public const ACTION_RESTORE_INVOICE_STATUS = 157;

    public const ACTION_IMPORT_INVOICE_STATUS = 158;

    public const ACTION_EXPORT_INVOICE_STATUS = 159;

    public const ACTION_INVOICE_STATUS_UPDATED_BY_CRON = 160;

    public const ACTION_ASSIGN_INVOICE_STATUS = 161;

    public const ACTION_UNASSIGN_INVOICE_STATUS = 162;

    // Comment Management
    public const ACTION_CREATE_COMMENT = 163;

    public const ACTION_UPDATE_COMMENT = 164;

    public const ACTION_VIEW_COMMENT = 165;

    public const ACTION_DELETE_COMMENT = 166;

    public const ACTION_FORCE_DELETE_COMMENT = 167;

    public const ACTION_RESTORE_COMMENT = 168;

    // Tag Management
    public const ACTION_CREATE_TAG = 169;

    public const ACTION_UPDATE_TAG = 170;

    public const ACTION_DELETE_TAG = 171;

    public const ACTION_RESTORE_TAG = 172;

    public const ACTION_FORCE_DELETE_TAG = 173;

    // Registration Interest Management
    public const ACTION_CREATE_REGISTRATION_INTEREST = 174;

    public const ACTION_VIEW_REGISTRATION_INTEREST = 175;

    public const ACTION_DELETE_REGISTRATION_INTEREST = 176;

    public const ACTION_FORCE_DELETE_REGISTRATION_INTEREST = 177;

    public const ACTION_RESTORE_REGISTRATION_INTEREST = 178;

    // Invoice Management
    public const ACTION_CREATE_INVOICE = 179;

    public const ACTION_UPDATE_INVOICE = 180;

    public const ACTION_VIEW_INVOICE = 181;

    public const ACTION_DELETE_INVOICE = 182;

    public const ACTION_FORCE_DELETE_INVOICE = 183;

    public const ACTION_RESTORE_INVOICE = 184;

    public const ACTION_IMPORT_INVOICE = 185;

    public const ACTION_EXPORT_INVOICE = 186;

    public const ACTION_INVOICE_UPDATED_BY_CRON = 187;

    public const ACTION_CHANGE_INVOICE_STATUS = 188;

    public const ACTION_SEND_INVOICE = 189;

    public const ACTION_MARK_INVOICE_PAID = 190;

    public const ACTION_MARK_INVOICE_UNPAID = 191;

    // Invoice Item Management
    public const ACTION_CREATE_INVOICE_ITEM = 192;

    public const ACTION_UPDATE_INVOICE_ITEM = 193;

    public const ACTION_VIEW_INVOICE_ITEM = 194;

    public const ACTION_DELETE_INVOICE_ITEM = 195;

    public const ACTION_FORCE_DELETE_INVOICE_ITEM = 196;

    public const ACTION_RESTORE_INVOICE_ITEM = 197;

    public const ACTION_IMPORT_INVOICE_ITEM = 198;

    public const ACTION_EXPORT_INVOICE_ITEM = 199;

    public const ACTION_INVOICE_ITEM_UPDATED_BY_CRON = 200;

    // Pipeline Status Management
    public const ACTION_CREATE_PIPELINE_STATUS = 201;

    public const ACTION_UPDATE_PIPELINE_STATUS = 202;

    public const ACTION_VIEW_PIPELINE_STATUS = 203;

    public const ACTION_DELETE_PIPELINE_STATUS = 204;

    public const ACTION_FORCE_DELETE_PIPELINE_STATUS = 205;

    public const ACTION_RESTORE_PIPELINE_STATUS = 206;

    public const ACTION_IMPORT_PIPELINE_STATUS = 207;

    public const ACTION_EXPORT_PIPELINE_STATUS = 208;

    public const ACTION_PIPELINE_STATUS_UPDATED_BY_CRON = 209;

    public const ACTION_ASSIGN_PIPELINE_STATUS = 210;

    public const ACTION_UNASSIGN_PIPELINE_STATUS = 211;

    // Pipeline Management
    public const ACTION_CREATE_PIPELINE = 212;

    public const ACTION_UPDATE_PIPELINE = 213;

    public const ACTION_VIEW_PIPELINE = 214;

    public const ACTION_DELETE_PIPELINE = 215;

    public const ACTION_FORCE_DELETE_PIPELINE = 216;

    public const ACTION_RESTORE_PIPELINE = 217;

    public const ACTION_IMPORT_PIPELINE = 218;

    public const ACTION_EXPORT_PIPELINE = 219;

    public const ACTION_PIPELINE_UPDATED_BY_CRON = 220;

    public const ACTION_ASSIGN_PIPELINE = 221;

    public const ACTION_UNASSIGN_PIPELINE = 222;

    public const ACTION_CHANGE_PIPELINE_STATUS = 223;

    // Pipeline Stage Management
    public const ACTION_CREATE_PIPELINE_STAGE = 224;

    public const ACTION_UPDATE_PIPELINE_STAGE = 225;

    public const ACTION_VIEW_PIPELINE_STAGE = 226;

    public const ACTION_DELETE_PIPELINE_STAGE = 227;

    public const ACTION_FORCE_DELETE_PIPELINE_STAGE = 228;

    public const ACTION_RESTORE_PIPELINE_STAGE = 229;

    public const ACTION_IMPORT_PIPELINE_STAGE = 230;

    public const ACTION_EXPORT_PIPELINE_STAGE = 231;

    public const ACTION_PIPELINE_STAGE_UPDATED_BY_CRON = 232;

    public const ACTION_ASSIGN_PIPELINE_STAGE = 233;

    public const ACTION_UNASSIGN_PIPELINE_STAGE = 234;

    public const ACTION_REORDER_PIPELINE_STAGE = 235;

    // Deal Status Management
    public const ACTION_CREATE_DEAL_STATUS = 236;

    public const ACTION_UPDATE_DEAL_STATUS = 237;

    public const ACTION_VIEW_DEAL_STATUS = 238;

    public const ACTION_DELETE_DEAL_STATUS = 239;

    public const ACTION_FORCE_DELETE_DEAL_STATUS = 240;

    public const ACTION_RESTORE_DEAL_STATUS = 241;

    public const ACTION_IMPORT_DEAL_STATUS = 242;

    public const ACTION_EXPORT_DEAL_STATUS = 243;

    public const ACTION_DEAL_STATUS_UPDATED_BY_CRON = 244;

    public const ACTION_ASSIGN_DEAL_STATUS = 245;

    public const ACTION_UNASSIGN_DEAL_STATUS = 246;

    // Deal Management
    public const ACTION_CREATE_DEAL = 247;

    public const ACTION_UPDATE_DEAL = 248;

    public const ACTION_VIEW_DEAL = 249;

    public const ACTION_DELETE_DEAL = 250;

    public const ACTION_FORCE_DELETE_DEAL = 251;

    public const ACTION_RESTORE_DEAL = 252;

    public const ACTION_IMPORT_DEAL = 253;

    public const ACTION_EXPORT_DEAL = 254;

    public const ACTION_DEAL_UPDATED_BY_CRON = 255;

    public const ACTION_ASSIGN_DEAL = 256;

    public const ACTION_UNASSIGN_DEAL = 257;

    public const ACTION_CHANGE_DEAL_STATUS = 258;

    // Additional Comment Management
    public const ACTION_IMPORT_COMMENT = 259;

    public const ACTION_EXPORT_COMMENT = 260;

    // Registration Interest Management
    public const ACTION_EXPORT_REGISTRATION_INTEREST = 261;

    // Additional Tag Management
    public const ACTION_IMPORT_TAG = 262;

    public const ACTION_EXPORT_TAG = 263;

    // Ticket Priority Management
    public const ACTION_CREATE_TICKET_PRIORITY = 264;

    public const ACTION_UPDATE_TICKET_PRIORITY = 265;

    public const ACTION_VIEW_TICKET_PRIORITY = 266;

    public const ACTION_DELETE_TICKET_PRIORITY = 267;

    public const ACTION_FORCE_DELETE_TICKET_PRIORITY = 268;

    public const ACTION_RESTORE_TICKET_PRIORITY = 269;

    public const ACTION_IMPORT_TICKET_PRIORITY = 270;

    public const ACTION_EXPORT_TICKET_PRIORITY = 271;

    public const ACTION_TICKET_PRIORITY_UPDATED_BY_CRON = 272;

    // Ticket Status Management
    public const ACTION_CREATE_TICKET_STATUS = 273;

    public const ACTION_UPDATE_TICKET_STATUS = 274;

    public const ACTION_VIEW_TICKET_STATUS = 275;

    public const ACTION_DELETE_TICKET_STATUS = 276;

    public const ACTION_FORCE_DELETE_TICKET_STATUS = 277;

    public const ACTION_RESTORE_TICKET_STATUS = 278;

    public const ACTION_IMPORT_TICKET_STATUS = 279;

    public const ACTION_EXPORT_TICKET_STATUS = 280;

    public const ACTION_TICKET_STATUS_UPDATED_BY_CRON = 281;

    // Ticket Management
    public const ACTION_CREATE_TICKET = 282;

    public const ACTION_UPDATE_TICKET = 283;

    public const ACTION_VIEW_TICKET = 284;

    public const ACTION_DELETE_TICKET = 285;

    public const ACTION_FORCE_DELETE_TICKET = 286;

    public const ACTION_RESTORE_TICKET = 287;

    public const ACTION_IMPORT_TICKET = 288;

    public const ACTION_EXPORT_TICKET = 289;

    public const ACTION_TICKET_UPDATED_BY_CRON = 290;

    public const ACTION_ASSIGN_TICKET = 291;

    public const ACTION_UNASSIGN_TICKET = 292;

    public const ACTION_CHANGE_TICKET_STATUS = 293;

    public const ACTION_CHANGE_TICKET_PRIORITY = 294;

    // Label Management
    public const ACTION_CREATE_LABEL = 295;

    public const ACTION_UPDATE_LABEL = 296;

    public const ACTION_VIEW_LABEL = 297;

    public const ACTION_DELETE_LABEL = 298;

    public const ACTION_FORCE_DELETE_LABEL = 299;

    public const ACTION_RESTORE_LABEL = 300;

    public const ACTION_IMPORT_LABEL = 301;

    public const ACTION_EXPORT_LABEL = 302;

    public const ACTION_LABEL_UPDATED_BY_CRON = 303;

    public const ACTION_ASSIGN_LABEL = 304;

    public const ACTION_UNASSIGN_LABEL = 305;

    // Additional Task Management
    public const ACTION_RESOLVE_TICKET = 306;

    public const ACTION_UNRESOLVE_TICKET = 307;

    // System Maintenance
    public const ACTION_ENABLE_MAINTENANCE = 308;

    public const ACTION_DISABLE_MAINTENANCE = 309;

    // Backup management
    public const ACTION_CREATE_BACKUP = 310;

    public const ACTION_DELETE_BACKUP = 311;

    public const ACTION_RESTORE_BACKUP = 312;

    public const ACTION_IMPORT_BACKUP = 313;

    public const ACTION_EXPORT_BACKUP = 314;

    // API Management
    public const ACTION_CREATE_API_TOKEN = 315;

    public const ACTION_UPDATE_API_TOKEN = 316;

    public const ACTION_REVOKE_API_TOKEN = 317;

    // Activity Management
    public const ACTION_CREATE_ACTIVITY = 318;

    public const ACTION_UPDATE_ACTIVITY = 319;

    public const ACTION_DELETE_ACTIVITY = 320;

    public const ACTION_FORCE_DELETE_ACTIVITY = 321;

    public const ACTION_RESTORE_ACTIVITY = 322;

    public const ACTION_EXPORT_ACTIVITY = 323;

    // Interaction Log Management
    public const ACTION_CREATE_INTERACTION_LOG = 324;

    public const ACTION_UPDATE_INTERACTION_LOG = 325;

    public const ACTION_DELETE_INTERACTION_LOG = 326;

    public const ACTION_FORCE_DELETE_INTERACTION_LOG = 327;

    // Attachment Managemetn
    public const ACTION_CREATE_ATTACHMENT = 328;

    public const ACTION_DELETE_ATTACHMENT = 329;

    public const ACTION_FORCE_DELETE_ATTACHMENT = 330;

    public const ACTION_RESTORE_ATTACHMENT = 331;

    // Notification Broadcast Management
    public const ACTION_CREATE_NOTIFICATION_BROADCAST = 332;

    public const ACTION_UPDATE_NOTIFICATION_BROADCAST = 333;

    public const ACTION_VIEW_NOTIFICATION_BROADCAST = 334;

    public const ACTION_DELETE_NOTIFICATION_BROADCAST = 335;

    public const ACTION_FORCE_DELETE_NOTIFICATION_BROADCAST = 336;

    public const ACTION_RESTORE_NOTIFICATION_BROADCAST = 337;

    public const ACTION_SEND_NOTIFICATION_BROADCAST = 338;

    public const ACTION_MERGE_COMPANY = 339;

    public const ACTION_MERGE_CONTACT = 340;

    // Permission Management
    public const ACTION_CREATE_PERMISSION = 341;

    public const ACTION_UPDATE_PERMISSION = 342;

    public const ACTION_DELETE_PERMISSION = 343;

    public const ACTION_FORCE_DELETE_PERMISSION = 344;

    public const ACTION_RESTORE_PERMISSION = 345;

    public const ACTION_ASSIGN_PERMISSION = 346;

    // Additional Deal Management
    public const ACTION_UPDATE_DEAL_STAGE = 347;

    // Settings Management
    public const ACTION_UPDATE_GENERAL_SETTINGS = 348;

    public const ACTION_UPDATE_SYSTEM_SETTINGS = 349;

    public const ACTION_UPDATE_SECURITY_SETTINGS = 350;

    // Activity Log Viewer Management
    public const ACTION_EXPORT_ACTIVITY_LOG = 351;

    public const ACTION_DELETE_ACTIVITY_LOG = 352;

    // Organisation Management
    public const ACTION_CREATE_ORGANISATION = 353;

    public const ACTION_UPDATE_ORGANISATION = 354;

    public const ACTION_DELETE_ORGANISATION = 355;

    public const ACTION_RESTORE_ORGANISATION = 356;

    public const ACTION_FORCE_DELETE_ORGANISATION = 357;

    // Impersonation
    public const ACTION_START_IMPERSONATION = 358;

    public const ACTION_STOP_IMPERSONATION = 359;

    // Report Management
    public const ACTION_CREATE_REPORT = 360;

    public const ACTION_UPDATE_REPORT = 361;

    public const ACTION_VIEW_REPORT = 362;

    public const ACTION_DELETE_REPORT = 363;

    public const ACTION_FORCE_DELETE_REPORT = 364;

    public const ACTION_RESTORE_REPORT = 365;

    public const ACTION_IMPORT_REPORT = 366;

    public const ACTION_EXPORT_REPORT = 367;

    public const ACTION_REPORT_UPDATED_BY_CRON = 368;

    public const ACTION_RUN_REPORT = 369;

    // Passkey Management
    public const ACTION_CREATE_PASSKEY = 370;

    public const ACTION_REVOKE_PASSKEY = 371;

    // Comment Mention Management
    public const ACTION_MENTION_NOTIFIED = 372;

    // New Logging Actions should go here to be reviewed

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
        return $query->where(
            'action_id',
            $action
        );
    }

    /**
     * Get a snapshot of the log's attributes for audit purposes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'action_id',
            'data',
            'logged_in_user_id',
            'related_to_user_id',
            'created_at',
        ]);
    }

    /**
     * Get the human-readable label for a given action ID.
     */
    public static function actionLabel(int $actionId): string
    {
        return self::actionLabels()[$actionId] ?? 'Unknown action';
    }

    /**
     * Get a map of action ID to human-readable label, derived from the
     * ACTION_* class constants so it stays in sync automatically.
     *
     * @return array<int, string>
     */
    public static function actionLabels(): array
    {
        static $labels = null;

        if ($labels !== null) {
            return $labels;
        }

        $labels = [];

        foreach ((new \ReflectionClass(self::class))->getConstants() as $name => $value) {
            if (! str_starts_with($name, 'ACTION_') || ! is_int($value)) {
                continue;
            }

            $labels[$value] = ucfirst(strtolower(str_replace('_', ' ', substr($name, 7))));
        }

        return $labels;
    }
}
