<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractRequest extends FormRequest
{

    protected function passedValidation(): void
    {
        foreach ($this->validationData() as $key => $value) {
            $setMethod = 'set' . ucfirst($key);
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($value);
            }
        }
    }
}
