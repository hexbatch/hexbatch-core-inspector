<?php

namespace App\Helpers;

use Hexbatch\Things\Helpers\IThingOwner;

class TestOwner implements IThingOwner
{

    public function getOwnerId(): int
    {
        // TODO: Implement getOwnerId() method.
    }

    public static function getOwnerType(): string
    {
        // TODO: Implement getOwnerType() method.
    }

    public static function resolveOwner(int $action_id): IThingOwner
    {
        // TODO: Implement resolveOwner() method.
    }
}
