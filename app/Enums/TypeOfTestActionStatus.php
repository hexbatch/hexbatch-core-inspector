<?php
namespace App\Enums;
/**
 * postgres enum type_of_test_action_status
 */
enum TypeOfTestActionStatus : string {
    case ACTION_PENDING = 'action_pending';
    case ACTION_SUCCESS = 'action_success';
    case ACTION_FAIL = 'action_fail';
    case ACTION_ERROR = 'action_error';
    case ACTION_WAITING = 'action_waiting';


    public static function tryFromInput(string|int|bool|null $test ) : TypeOfTestActionStatus {
        $maybe  = TypeOfTestActionStatus::tryFrom($test);
        if (!$maybe ) {
            $delimited_values = implode('|',array_column(TypeOfTestActionStatus::cases(),'value'));
            throw new \InvalidArgumentException(__("msg.invalid_enum",['ref'=>$test,'enum_list'=>$delimited_values]));
        }
        return $maybe;
    }
}


