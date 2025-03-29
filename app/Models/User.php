<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingCallback;
use Hexbatch\Things\Models\ThingCallplate;
use Hexbatch\Things\Models\ThingHook;
use Hexbatch\Things\Models\ThingSetting;
use Hexbatch\Things\Models\ThingStat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property string name
 */
class User extends Authenticatable implements IThingOwner
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    const OWNER_TYPE = 'user';

    public function getOwnerId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function getOwnerType(): string
    {
        return static::OWNER_TYPE;
    }

    public static function resolveOwner(int $owner_id): IThingOwner
    {
        $ret = User::find($owner_id);
        if (!$ret) {
            throw new \InvalidArgumentException("user not found using $owner_id");
        }
        return $ret;
    }

    public static function registerOwner(): void
    {
        Thing::registerOwnerType(static::class);
        ThingCallback::registerOwnerType(static::class);
        ThingCallplate::registerOwnerType(static::class);
        ThingHook::registerOwnerType(static::class);
        ThingSetting::registerOwnerType(static::class);
        ThingStat::registerOwnerType(static::class);
    }
}
