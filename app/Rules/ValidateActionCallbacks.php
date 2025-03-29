<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateActionCallbacks implements ValidationRule,DataAwareRule
{
    const WHITELIST = [
        '\App\Models\TestActionDatum::runBasicOrLogic',
    ];

    public static function isValidCallback(?string $class_name,string $function_name) : bool {
        if (empty($class_name)) {
            return in_array($function_name,static::WHITELIST);
        }
        $comp = [];
        foreach (static::WHITELIST as $what) {
            $parts = explode('::',$what);
            if (count($parts) === 1) {continue;}
            $comp[$parts[0]] = $parts[1];
        }

        foreach ($comp as $class => $method) {
            if ($class === $class_name && $method === $function_name) {
                return true;
            }
        }
        return false;
    }
    /**
     * All the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($attribute === 'test_action_run_class') {
            $class_name = $value;
            $function_name = $this->data['test_action_run_function']??null;
            if (!$function_name) {
                $fail('Cannot have an action function without a class');
            }
            if (!$class_name && !$function_name) {
                $fail('Cannot have both an empty class and function ');
            }
            if (!static::isValidCallback(class_name: $class_name,function_name: $function_name)) {
                $fail("$class_name::$function_name is not on the whitelist");
            }
        } elseif ($attribute === 'test_action_run_function') {
            $class_name = $this->data['test_action_run_class']??null;
            $function_name = $value;
            if (!$function_name) {
                $fail('Cannot have an empty function');
            }
            if (!static::isValidCallback(class_name: $class_name,function_name: $function_name)) {
                $fail("$class_name::$function_name is not on the whitelist");
            }
        }
    }


    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}
