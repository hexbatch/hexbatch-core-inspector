<?php

namespace App\Helpers\TestActions;

use App\Enums\TypeOfTestActionStatus;
use App\Helpers\TestOptions;
use App\Interfaces\ITestActionInnards;
use App\Models\TestActionDatum;
use BlueM\Tree;
use Hexbatch\Things\Interfaces\IThingAction;


abstract class BaseTest implements ITestActionInnards
{

    abstract public static function create(?TestActionDatum $parent = null,?TestOptions $options = null) : TestActionDatum;
    public static function getChildrenTreeInnard(TestActionDatum $action, ?string $key = null): ?Tree
    {
        return null;
    }

    public static function runActionInnard(TestActionDatum $action, array $data): void
    {
        $action->test_action_content = array_merge($data,$action->test_action_content?->getArrayCopy()??[]);

        foreach ($action->test_action_content as $what) {
            if ($what) {$action->action_status = TypeOfTestActionStatus::ACTION_SUCCESS; return;}
        }

        $action->action_status = TypeOfTestActionStatus::ACTION_FAIL;

    }



    public static function setChildActionResultInnard(TestActionDatum $action, IThingAction $child): void
    {
        $action->test_action_content = array_merge($child->getActionResult(),$action->getActionResult());
    }

    public static function addDataBeforeRunInnard(TestActionDatum $action, array $data): void
    {
        $action->test_action_content = array_merge($data,$action->test_action_content->getArrayCopy());
    }

    public static function getRenderHtmlInnard(TestActionDatum $action): ?string
    {
        //move to blade??
        return sprintf('<span style="color: %s">%s</span>',$action->test_action_color,$action->getActionRef());
    }


    public static function getActionRefInnard(TestActionDatum $action): int
    {
        return sprintf("%s %s #%s",$action->test_action_type,$action->test_action_name,$action->id);
    }

    public static function getActionResultInnard(TestActionDatum $action): array
    {
        return $action->test_action_content?->getArrayCopy()??[];
    }
}
