<?php

namespace App\Interfaces;

use App\Helpers\TestOptions;
use App\Models\TestActionDatum;
use BlueM\Tree;
use Hexbatch\Things\Interfaces\IThingAction;

interface ITestActionInnards
{
    public static function create(?TestActionDatum $parent = null,?TestOptions $options = null) : TestActionDatum;

    public static function getChildrenTreeInnard(TestActionDatum $action,?string $key = null) : ?Tree;

    public static function runActionInnard(TestActionDatum $action,array $data): void;
    public static function getActionResultInnard(TestActionDatum $action): array;
    public static function getPreRunDataInnard(TestActionDatum $action): array;


    public static function setChildActionResultInnard(TestActionDatum $action,IThingAction $child) : void ;

    public static function addDataBeforeRunInnard(TestActionDatum $action,array $data): void;

    public static function getRenderHtmlInnard(TestActionDatum $action): ?string;
    public static function getActionRefInnard(TestActionDatum $action): ?string;


}
