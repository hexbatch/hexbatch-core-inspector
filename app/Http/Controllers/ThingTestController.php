<?php

namespace App\Http\Controllers;

use App\Enums\TypeOfTestActionStatus;
use App\Helpers\TestActions\SimpleRoot;
use App\Helpers\TestOptions;
use App\Http\Requests\TestActionDataRequest;
use App\Models\TestActionDatum;
use Hexbatch\Things\Models\Thing;
use Illuminate\Http\Request;


class ThingTestController extends Controller
{

    public function create_action(TestActionDataRequest $request) {
        $node = new TestActionDatum();
        $node->fill($request->validated());
        $node->save();
        $node->refresh();
        return response()->json(['success'=>true,'action'=>$node,'message'=>'created action']);
    }

    public function create_canned_action(Request $request) {
        $options = TestOptions::makeFromRequest(request: $request);
        switch ($template = $request->request->getString('action_template')) {
            case SimpleRoot::TEMPLATE_NAME: {
                $node = SimpleRoot::create(options: $options);
                break;
            }
            default: {
                throw new \InvalidArgumentException("Invalid canned action: $template");
            }
        }

        return response()->json(['success'=>true,'action'=>$node,'message'=>'created canned action']);
    }

    public function update_action(TestActionDatum $datum,TestActionDataRequest $request) {
        $datum->fill($request->validated());
        $datum->save();
        $datum->refresh();
        return response()->json(['success'=>true,'action'=>$datum,'message'=>'updated action']);
    }

    public function destroy_action(TestActionDatum $datum) {
        $datum->delete();
        return response()->json(['success'=>true,'action'=>$datum,'message'=>'deleted action']);
    }

    public function show_action(TestActionDatum $datum) {
        return response()->json(['success'=>true,'action'=>$datum,'message'=>'show action']);
    }
    /**
     * @throws \Exception
     */
    public function create_thing(Request $request) {
        $name = $request->request->getString('action_name');
        $type = $request->request->getString('action_type');
        $options = TestOptions::makeFromRequest(request: $request);
        $tags = $options->getExtraTags()??[];

        $action = TestActionDatum::buildTestAction(
            is_root: true, test_action_type: $type, test_action_name: $name,
            status: TypeOfTestActionStatus::ACTION_PENDING
        )
            ->orderBy('id')
            ->first();

        if (!$action) {
            throw new \InvalidArgumentException("Action is not defined for $type:$name");
        }

        $hooker = Thing::buildFromAction(action: $action,extra_tags: $tags);
        return response()->json(['success'=>true,'hooker'=>$hooker,'message'=>'created thing']);
    }
}
