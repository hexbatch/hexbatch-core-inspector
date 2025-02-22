<?php

namespace App\Http\Controllers;

use App\Helpers\TestAction;
use Hexbatch\Things\Models\Thing;

class HookController extends Controller
{
    public function test_things() {
        $action = new TestAction();
        Thing::runAction(action: $action);
    }
}
