<?php

namespace App\Http\Controllers;

use App\Enums\TypeOfTestActionStatus;
use App\Models\TestActionDatum;
use Hexbatch\Things\Models\Thing;

class HookController extends Controller
{
    /**
     * @throws \Exception
     */
    public function test_things() {
        $action = TestActionDatum::buildTestAction(is_root: true, test_action_type: 'first', status: TypeOfTestActionStatus::ACTION_PENDING)->first();
        Thing::buildAction(action: $action);
    }
}
