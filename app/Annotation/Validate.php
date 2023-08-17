<?php

namespace App\Annotation;

#[\Attribute]
class Validate
{
    public function __construct(protected array $rules)
    {

    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
