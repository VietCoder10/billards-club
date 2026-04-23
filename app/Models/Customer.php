<?php

namespace App\Models;

use App\Components\CommonComponent;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $table = 'customers';
    protected $fillable = [
        'name',
        'phone',
        'avatar',
        'email',
        'email_verified_at',
        'password',
        'reset_password_token',
        'reset_password_token_expire',
        'last_login_at',
        'remember_token',
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
    protected $appends = ['avatar_url'];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'reset_password_token_expire' => 'datetime:Y/m/d H:i:s',
            'last_login_at' => 'datetime:Y/m/d H:i:s',
            'created_at' => 'datetime:Y/m/d H:i:s',
            'user_role' => 'integer',
            'password' => 'hashed',
        ];
    }

    protected function serializeDate($date)
    {
        return Carbon::parse($date);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? CommonComponent::getFullUrl($this->avatar) : url('/images/default-avatar.svg');
    }
}
