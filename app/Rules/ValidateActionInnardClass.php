<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateActionInnardClass implements ValidationRule,DataAwareRule
{

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
        if (class_exists($value)) {
            $interfaces = class_implements($value);

            if (!isset($interfaces['App\Interfaces\ITestActionInnards'])) {
                $fail("$value does not implement ITestActionInnards");
            }
        } else {
            $fail("$value is not a class, is this correct namespace?");
        }
    }


    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}
