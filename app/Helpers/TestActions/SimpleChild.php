<?php

namespace App\Helpers\TestActions;


use App\Helpers\TestOptions;
use App\Models\TestActionDatum;
use BlueM\Tree;
use Hexbatch\Things\Interfaces\IThingAction;
use Ramsey\Uuid\Uuid;


class SimpleChild extends BaseTest
{
    const string TEST_ACTION_NAME_STUB = 'sc-';
    public static function create(?TestActionDatum $parent = null,?TestOptions $options = null) : TestActionDatum
    {
        $root = '';
        if ($options?->getBaseName()) {
            $root = $options->getBaseName() .'-';
        }

        $node = new TestActionDatum();
        $node->parent_action_id = $parent?->id;
        $node->test_action_async = false;
        $node->test_action_constant = ['roses'=>'red','counter'=>1];
        $node->test_action_tags = ['roses','hippos','mark_pass'];
        $node->test_action_name = $root . static::TEST_ACTION_NAME_STUB . Uuid::uuid4()->toString();
        $node->test_action_innard_class = static::class;


        if ($options) {
            TestOptions::fillAction($options,$node);
        }

        $node->save();
        $node->refresh();
        return $node;
    }

    /** @return IThingAction[] */
    public static function getMoreSiblingActionsInnard(TestActionDatum $action): array
    {
        $ret = [];
        $generations = $action->getIntFromConstants(SimpleRoot::GENERATIONS_KEY);
        if ($generations > 1) {
            $options = new TestOptions(
                extra_tags: ['nu_thing'],
                extra_constant: [SimpleRoot::GENERATIONS_KEY=> $generations -100],
                base_name: 'spot-'.$action->getRootAction()->test_action_name);
            $options->setOwner($action->getActionOwner());

            $ret[] = SimpleChild::create(parent: $action, options: $options);
        }
        return $ret;
    }

    public static function getChildrenTreeInnard(TestActionDatum $action): ?Tree
    {
        //see how many generations we are on > 1
        $generations = $action->getIntFromConstants(SimpleRoot::GENERATIONS_KEY);
        if ($generations > 1) {
            $options = new TestOptions(extra_constant: [SimpleRoot::GENERATIONS_KEY=> $generations -1],base_name: $action->getRootAction()->test_action_name);
            $options->setOwner($action->getActionOwner());
            $node = SimpleChild::create(parent: $action, options: $options);
            $data[] = ['id' => $node->id, 'parent' => -1, 'title' => $node->test_action_name,'action'=>$node];

            return new Tree(
                $data,
                ['rootId' => -1]
            );
        }
        return null;
    }

}
