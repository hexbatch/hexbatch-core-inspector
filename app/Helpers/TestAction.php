<?php

namespace App\Helpers;

use BlueM\Tree;
use Carbon\Carbon;
use Hexbatch\Things\Helpers\IThingAction;
use Hexbatch\Things\Helpers\IThingOwner;
use Hexbatch\Things\Models\Enums\TypeOfThingStatus;

class TestAction implements IThingAction
{

    public function getActionStatus(): TypeOfThingStatus
    {
        // TODO: Implement getActionStatus() method.
    }

    public function getActionId(): int
    {
        // TODO: Implement getActionId() method.
    }

    public function getActionPriority(): int
    {
        // TODO: Implement getActionPriority() method.
    }

    public static function getActionType(): string
    {
        // TODO: Implement getActionType() method.
    }

    public function getChildrenTree(?string $key = null): Tree
    {
        // TODO: Implement getChildrenTree() method.
    }

    public function runAction(): void
    {
        // TODO: Implement runAction() method.
    }

    public function getDataByteRowsUsed(): int
    {
        // TODO: Implement getDataByteRowsUsed() method.
    }

    public function setLimitDataByteRows(int $limit): int
    {
        // TODO: Implement setLimitDataByteRows() method.
    }

    public function getActionOwner(): IThingOwner
    {
        // TODO: Implement getActionOwner() method.
    }

    public function getStartAt(): ?Carbon
    {
        // TODO: Implement getStartAt() method.
    }

    public function getInvalidAt(): ?Carbon
    {
        // TODO: Implement getInvalidAt() method.
    }

    public function isAsync(): bool
    {
        // TODO: Implement isAsync() method.
    }

    public function isMoreBuilding(): ?string
    {
        // TODO: Implement isMoreBuilding() method.
    }

    public function getActionResult(): ?array
    {
        // TODO: Implement getActionResult() method.
    }

    public function setChildActionResult(IThingAction $child): void
    {
        // TODO: Implement setChildActionResult() method.
    }

    public function getActionHttpCode(): int
    {
        // TODO: Implement getActionHttpCode() method.
    }

    public static function resolveAction(int $action_id): IThingAction
    {
        // TODO: Implement resolveAction() method.
    }
}
