<?php

namespace App\Helpers;

use App\Models\TestActionDatum;
use Illuminate\Http\Request;

class TestOptions
{
    public function __construct(
        protected ?bool $async = null,
        protected ?int $priority = null,
        protected ?int $start_offset = null,
        protected ?int $invalid_offset = null,
        protected ?int $data_limit = null,
        protected ?array $extra_content = null,
        protected ?array $extra_tags = null,
        protected ?array $extra_constant = null,
        protected ?string $color = null,
        protected ?string $test_name = null,
        protected ?string $base_name = null,
    )
    {

    }

    public function setBaseName(?string $base_name): void
    {
        $this->base_name = $base_name;
    }

    public function getBaseName(): ?string
    {
        return $this->base_name;
    }

    public function getAsync(): ?bool
    {
        return $this->async;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getStartOffset(): ?int
    {
        return $this->start_offset;
    }

    public function getInvalidOffset(): ?int
    {
        return $this->invalid_offset;
    }

    public function getDataLimit(): ?int
    {
        return $this->data_limit;
    }

    public function getExtraContent(): ?array
    {
        return $this->extra_content;
    }

    public function getExtraTags(): ?array
    {
        return $this->extra_tags;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getTestName(): ?string
    {
        return $this->test_name;
    }

    public function getExtraConstant(): ?array
    {
        return $this->extra_constant;
    }

    public function applyGivenToEmpty(TestActionDatum $action) :void {
        if(empty($this->async)) { $this->async = $action->test_action_async;}
        if(empty($this->priority)) { $this->priority = $action->test_action_priority;}
        if(empty($this->start_offset)) { $this->start_offset = $action->test_action_start_offset_seconds;}
        if(empty($this->invalid_offset)) { $this->invalid_offset = $action->test_action_invalid_offset_seconds;}
        if(empty($this->data_limit)) { $this->data_limit = $action->test_action_data_row_limit;}
        if(empty($this->extra_tags)) { $this->extra_tags = $action->test_action_tags->getArrayCopy();}
        if(empty($this->extra_constant)) { $this->extra_constant = $action->test_action_constant->getArrayCopy();}
        if(empty($this->color)) { $this->color = $action->test_action_color;}
    }

    public static function makeFromRequest(Request $request) : TestOptions {
        $async = $request->get('async');
        if ($async !== null) { $async = (bool)$async;}

        $priority = $request->get('priority');
        if ($priority !== null) { $priority = (int)$priority;}

        $start_offset = $request->get('start_offset');
        if ($start_offset !== null) { $start_offset = (int)$start_offset;}

        $invalid_offset = $request->get('invalid_offset');
        if ($invalid_offset !== null) { $invalid_offset = (int)$invalid_offset;}

        $data_limit = $request->get('data_limit');
        if ($data_limit !== null) { $data_limit = (int)$data_limit;}


        $content = $request->get('extra_content');
        if ($content !== null) {
            if (!is_array($content)) {
                $content = [$content];
            }
        }

        $tags = $request->get('extra_tags');
        if ($tags !== null) {
            if (!is_array($tags)) {
                $tags = [$tags];
            }
            foreach ($tags as $tag) {
                if (!is_string($tag) || empty($tag)) {
                    throw new \InvalidArgumentException("tags need to be strings");
                }
            }
        }

        $constant = $request->get('extra_constants');
        if ($constant !== null) {
            if (!is_array($constant)) {
                $constant = [$constant];
            }
        }

        $color = $request->get('color');
        if ($color !== null) { $color = (string)$color;}

        $name = $request->get('name');
        if ($name !== null) { $name = (string)$name;}


        return new static(
            async: $async,priority: $priority,start_offset: $start_offset,invalid_offset: $invalid_offset,data_limit:$data_limit,
            extra_content: $content,extra_tags: $tags,extra_constant: $constant,color: $color,test_name: $name
        );

    }

    public static function fillAction(TestOptions $options,TestActionDatum $action) {
        if ($options->getAsync() !== null) { $action->test_action_async = $options->getAsync();}
        if ($options->getPriority() !== null) { $action->test_action_priority = $options->getPriority();}
        if ($options->getStartOffset() !== null) { $action->test_action_start_offset_seconds = $options->getStartOffset();}
        if ($options->getInvalidOffset() !== null) { $action->test_action_invalid_offset_seconds = $options->getInvalidOffset();}
        if ($options->getDataLimit() !== null) { $action->test_action_data_row_limit = $options->getDataLimit();}
        if ($options->getColor() !== null) { $action->test_action_color = $options->getColor();}
        if ($options->getTestName() !== null) { $action->test_action_name = $options->getTestName();}

        if ($options->getExtraConstant() !== null) {
            $action->test_action_constant = array_merge($action->test_action_constant?->getArrayCopy()??[],$options->getExtraConstant());}

        if ($options->getExtraContent() !== null) {
            $action->test_action_content = array_merge($action->test_action_content?->getArrayCopy()??[],$options->getExtraContent());}

        if ($options->getExtraTags() !== null) {
            $action->test_action_tags = array_merge($action->test_action_tags?->getArrayCopy()??[],$options->getExtraTags());}
    }
}
