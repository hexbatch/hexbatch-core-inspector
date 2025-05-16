<?php

namespace App\Helpers\TestOwners;

use App\Models\User;
use Hexbatch\Things\Enums\TypeOfOwnerGroup;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingHook;

class OwnerFromUser implements IThingOwner
{
    const string OWNER_TYPE = 'user';
    public function __construct(
        protected User $user
    )
    {
    }

    public function getOwnerId(): int
    {
        return $this->user->getOwnerId();
    }

    public function getName(): string
    {
        return $this->user->getName();
    }

    /**
     * @param \Illuminate\Contracts\Database\Query\Builder $builder
     */
    public function setReadGroupBuilding($builder, string $connecting_table_name,
                                         string $connecting_owner_type_column, string $connecting_owner_id_column,
                                         TypeOfOwnerGroup $hint,?string $alias = null
    ) :void
    {
        $this->user->setReadGroupBuilding($builder,$connecting_table_name,$connecting_owner_type_column,$connecting_owner_id_column,$hint,$alias);
    }

    public function getTags() : array {
        return $this->user->getTags();
    }

    public static function getOwnerTypeStatic(): string
    {
        return static::OWNER_TYPE;
    }

    public function getOwnerType() : string {
        return static::OWNER_TYPE;
    }

    public static function resolveOwner(int $owner_id): IThingOwner
    {
        $ret = User::find($owner_id);
        if (!$ret) {
            throw new \InvalidArgumentException("user not found using $owner_id");
        }
        return new OwnerFromUser(user: $ret);
    }

    public static function registerOwner(): void
    {
        Thing::registerOwnerType(static::class);
        ThingHook::registerOwnerType(static::class);
    }
}
