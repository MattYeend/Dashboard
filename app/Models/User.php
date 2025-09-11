<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            "{$this->first_name}
             {$this->middle_name}
             {$this->last_name}"
        );
    }

    /**
     * Get the user's formal name.
     *
     * @return string
     */
    public function getFormalNameAttribute(): string
    {
        return trim(
            "{$this->title}
             {$this->first_name}
             {$this->middle_name}
             {$this->last_name}"
        );
    }

    /**
     * Get the user's short name.
     *
     * @return string
     */
    public function getShortNameAttribute(): string
    {
        $shortName = $this->first_name . ' ' . $this->last_name;

        return trim($shortName);
    }

    /**
     * Get the user's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->first_name ? $this->first_name : $this->email;
    }

    /**
     * Get the user's initials.
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $initials = $this->first_name[0] ?? '';
        $initials .= $this->last_name[0] ?? '';

        return strtoupper($initials);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
