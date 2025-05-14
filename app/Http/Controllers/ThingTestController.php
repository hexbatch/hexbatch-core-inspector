<?php

namespace App\Http\Controllers;

use App\Helpers\TestActions\SimpleRoot;
use App\Helpers\TestOptions;
use App\Helpers\Utilities;
use App\Http\Requests\TestActionDataRequest;
use App\Models\TestActionDatum;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\OpenApi\Things\ThingResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


class ThingTestController extends Controller
{


    public function create_action(TestActionDataRequest $request,IThingOwner $owner) {
        $node = new TestActionDatum();
        $node->fill($request->validated());
        $node->owner_user_id = $owner->getOwnerId();
        $node->save();
        $node->refresh();
        return response()->json(['success'=>true,'action'=>$node,'message'=>'created action']);
    }

    public function create_canned_action(Request $request,IThingOwner $owner) {
        $options = TestOptions::makeFromRequest(request: $request);
        $options->setOwner($owner);
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

    public function create_cloned_action(TestActionDatum $action,IThingOwner $owner,Request $request) {

        $options = TestOptions::makeFromRequest(request: $request);

        $options->applyGivenToEmpty(action: $action);
        $options->setOwner(owner: $owner);
        $node = $action->getInnardClass()::create(options: $options);

        return response()->json(['success'=>true,'action'=>$node,'message'=>'created cloned action']);
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
    public function make_thing(TestActionDatum $action,IThingOwner $owner,Request $request) {
        $options = TestOptions::makeFromRequest(request: $request);
        $tags = $options->getExtraTags()??[];

        $root = Thing::buildFromAction(action: $action,owner: $owner,extra_tags: $tags);
        $root->refresh();
        return response()->json(new ThingResponse(thing:$root,b_include_hooks: true,b_include_children: true));
    }
}
