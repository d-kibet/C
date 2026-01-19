<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;

/**
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $unreadNotifications
 */
class User extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function getpermissionGroups(){
        $permission_groups = DB::table('permissions')->select('group_name')->groupBy('group_name')->get();
        return $permission_groups;
    } // End Method

    public static function getpermissionByGroupName($group_name){
        $permissions = DB::table('permissions')
                        ->select('name','id')
                        ->where('group_name',$group_name)
                        ->get();
          return $permissions;
    }// End Method

    public static function roleHasPermissions($role, $permissions){

        $hasPermission = true;
        foreach($permissions as $permission){
            if (!$role->hasPermissionTo($permission->name)) {
                $hasPermission = false;
                return $hasPermission;
            }
            return $hasPermission;
        }
    }// End Method

    /**
     * Get the user's notification preferences
     */
    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get or create notification preferences
     */
    public function getNotificationPreferences(): NotificationPreference
    {
        // Load relationship if not loaded
        if (!$this->relationLoaded('notificationPreferences')) {
            $this->load('notificationPreferences');
        }

        // If preferences exist, return them
        if ($this->notificationPreferences) {
            return $this->notificationPreferences;
        }

        // Create default preferences using firstOrCreate to avoid duplicates
        return NotificationPreference::firstOrCreate(
            ['user_id' => $this->id],
            [
                'email_enabled' => true,
                'sms_enabled' => true,
                'database_enabled' => true,
                'overdue_notifications' => true,
                'payment_reminders' => true,
                'pickup_notifications' => true,
                'followup_reminders' => true,
                'overdue_notification_interval' => 5,
                'max_notifications_per_day' => 50,
                'daily_digest' => false,
            ]
        );
    }

    /**
     * Route notification for SMS channel (uses phone number)
     */
    public function routeNotificationForSms()
    {
        return $this->phone;
    }
}
