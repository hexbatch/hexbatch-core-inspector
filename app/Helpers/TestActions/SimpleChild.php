<?php

namespace App\Helpers\TestActions;


use App\Helpers\TestOptions;
use App\Models\TestActionDatum;
use BlueM\Tree;
use Ramsey\Uuid\Uuid;


class SimpleChild extends BaseTest
{
    const TEST_ACTION_NAME_STUB = 'sc-';
    public static function create(?TestActionDatum $parent = null,?TestOptions $options = null) : TestActionDatum
    {
        $node = new TestActionDatum();
        $node->parent_action_id = $parent?->id;
        $node->test_action_async = false;
        $node->test_action_content = ['roses'=>'red','counter'=>1];
        $node->test_action_tags = ['roses','hippos'];
        $node->test_action_name = static::TEST_ACTION_NAME_STUB . Uuid::uuid4()->toString();
        $node->test_action_innard_class = static::class;


        if ($options) {
            TestOptions::fillAction($options,$node);
        }

        $node->save();
        $node->refresh();
        return $node;
    }

    public static function getChildrenTreeInnard(TestActionDatum $action, ?string $key = null): ?Tree
    {
        if ($key === null) {
            //see how many generations we are on > 1
            $generations = $action->getIntFromConstants(SimpleRoot::GENERATIONS_KEY);
            if ($generations > 1) {
                $node = SimpleChild::create(parent: $action,options: new TestOptions(extra_constant: [SimpleRoot::GENERATIONS_KEY=> $generations -1]));
                $data[] = ['id' => $node->id, 'parent' => -1, 'title' => $node->test_action_name,'action'=>$node];

                return new Tree(
                    $data,
                    ['rootId' => -1]
                );
            }
        }
        return null;
    }

}
