<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Helpers\TestOwners\OwnerFromUser;
use Hexbatch\Things\Enums\TypeOfOwnerGroup;
use Hexbatch\Things\Interfaces\IThingOwner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property string name
 * @property string username
 * @property string email
 * @property string created_at
 * @property string ref_uuid
 * @property UserFlag[] flags_of_user

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

    public function flags_of_user() : HasMany {
        return $this->hasMany(UserFlag::class,'flagged_user_id','id');
    }

    public function getOwnerId(): int
    {
        return $this->id;
    }

    public function getOwnerUuid() : string {
        return $this->ref_uuid;
    }

    public function getOwnerType() : string {
        return OwnerFromUser::OWNER_TYPE;
    }
    public static function getOwnerTypeStatic(): string
    {
        return OwnerFromUser::getOwnerTypeStatic();
    }

    public static function resolveOwner(int $owner_id): IThingOwner
    {
        return OwnerFromUser::resolveOwner(owner_id: $owner_id);
    }

    public static function resolveOwnerFromUiid(string $uuid) : IThingOwner {
        return OwnerFromUser::resolveOwnerFromUiid(uuid: $uuid);
    }

    public static function registerOwner(): void
    {
        OwnerFromUser::registerOwner();
    }

    public function getName() :string {
        return $this->name? : $this->username;
    }

    /**
     * @param \Illuminate\Contracts\Database\Query\Builder $builder
     */
    public function setReadGroupBuilding($builder, string $connecting_table_name,
                                         string $connecting_owner_type_column, string $connecting_owner_id_column,
                                         TypeOfOwnerGroup $hint,?string $alias = null
    ) :void
    {
        if (!$alias) {$alias = 'gu';}
        $owner_type = $this->getOwnerType();
        if ($hint !== TypeOfOwnerGroup::HOOK_CALLBACK_CREATION) {
            $builder->join("users as $alias",
                /** @param JoinClause $join */
                function ($join)
                use ($owner_type, $connecting_table_name, $connecting_owner_type_column, $connecting_owner_id_column,$alias) {
                    $join
                        ->on("$alias.id", '=', "$connecting_table_name.$connecting_owner_id_column")
                        /** @param \Illuminate\Database\Query\Builder $query */
                        ->where("$connecting_table_name.$connecting_owner_type_column", $owner_type);
                }
            );
        } else {
            $builder->leftJoin("users as $alias",
                /** @param JoinClause $join */
                function ($join)
                use ($owner_type, $connecting_table_name, $connecting_owner_type_column, $connecting_owner_id_column,$alias) {
                    $join
                        ->on("$alias.id", '=', "$connecting_table_name.$connecting_owner_id_column")
                        /** @param \Illuminate\Database\Query\Builder $query */
                        ->where("$connecting_table_name.$connecting_owner_type_column", $owner_type);
                }
            );
        }
    }

    /**
     * @return string[]
     */
    public function getTags() : array {
        return $this->flags_of_user()->orderBy('flag_name')->pluck('flag_name')->toArray();
    }
}
