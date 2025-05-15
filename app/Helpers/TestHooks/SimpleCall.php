<?php

namespace App\Helpers\TestHooks;


use Hexbatch\Things\Interfaces\ICallResponse;
use Hexbatch\Things\Interfaces\IHookCode;
use Symfony\Component\HttpFoundation\Response as CodeOf;

class SimpleCall implements IHookCode
{

    protected array $internal_data = [];

    public function getCode(): int
    {
        if ($this->internal_data['make_bad']??false) {
            return CodeOf::HTTP_FORBIDDEN;
        }
        return CodeOf::HTTP_OK;
    }


    public function getData(): ?array
    {
        return $this->internal_data;
    }

    public static function runHook(array $header, array $body): ICallResponse
    {
        $node = new static();
        $node->internal_data = array_merge($header,$body,['some_constant'=>'apples']);
        return $node;
    }
}
