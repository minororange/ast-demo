<?php

declare(strict_types=1);

namespace App\Composer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

class ArrayNodeBuilder
{
    public function build(array $array): Array_
    {
        foreach ($array as $key => $value) {
            $arrayItems[] = new ArrayItem($this->cast($value), $this->cast($key));
        }

        return new Array_($arrayItems ?? []);
    }

    private function cast(mixed $value): ?Expr
    {
        return match (gettype($value)) {
            'boolean' => new LNumber(intval($value)),
            'integer' => new LNumber($value),
            'double' => new String_(strval($value)),
            'string' => new String_($value),
            'array' => $this->build($value),
            default => null
        };
    }
}
