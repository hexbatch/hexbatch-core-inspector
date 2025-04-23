<?php

namespace App\Helpers\TestOwners;

use App\Models\User;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingCallback;
use Hexbatch\Things\Models\ThingCallplate;
use Hexbatch\Things\Models\ThingHook;
use Hexbatch\Things\Models\ThingSetting;
use Hexbatch\Things\Models\ThingStat;

class OwnerFromUser implements IThingOwner
{
    const OWNER_TYPE = 'user';
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

    public function getTags() : array {
        return $this->user->getTags();
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
        return new OwnerFromUser(user: $ret);
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
