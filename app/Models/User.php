<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Components\CommonComponent;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'user_role',
        'password',
        'reset_password_token',
        'reset_password_token_expire',
        'last_login_at',
        'sort_number'
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
    protected $appends = ['avatar_url', 'role_label'];
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
    public function getRoleLabelAttribute()
    {
        return UserRole::getLabel($this->user_role);
    }
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? CommonComponent::getFullUrl($this->avatar) : url('/images/default-avatar.svg');
    }
}
