<?php

namespace App\Helpers\TestActions;


use App\Helpers\TestOptions;
use App\Models\TestActionDatum;
use BlueM\Tree;
use Ramsey\Uuid\Uuid;


class SimpleRoot extends BaseTest
{

    const TEMPLATE_NAME = 'simple_root';

    const TEST_ACTION_NAME_STUB = 'sr-';

    const GENERATIONS_KEY = 'generations';
    const DEFAULT_GENERATIONS = 2;
    public static function create(?TestActionDatum $parent = null,?TestOptions $options = null) : TestActionDatum
    {
        $node = new TestActionDatum();
        $node->parent_action_id = $parent?->id;
        $node->test_action_async = false;
        $node->test_action_content = ['bedtime'=>21];
        $node->test_action_constant = ['roses'=>'blue','apples'=>2,'counter'=>1];
        $node->test_action_tags = ['simple','hippos'];
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
            $override_generations = $action->getIntFromConstants(static::GENERATIONS_KEY);
            if (!$override_generations) { $override_generations = static::DEFAULT_GENERATIONS;}
            $options = new TestOptions(extra_constant: [static::GENERATIONS_KEY=>$override_generations],base_name: $action->test_action_name);
            $options->setOwner($action->getActionOwner());
            $node = SimpleChild::create(parent: $action,options: $options);
            $child_tree = $node->getChildrenTree();

            $data[] = ['id' => $node->id, 'parent' => -1, 'title' => $node->test_action_name,'action'=>$node];
            foreach ($child_tree?->getNodes()??[] as $c) {
                $node_arr = $c->toArray();
                if ($node_arr['parent'] === -1) {$node_arr['parent'] = $node->id;}
                $data[] = $node_arr;
            }


            return new Tree(
                $data,
                ['rootId' => -1]
            );

        }
        return null;
    }


}
