<?php


namespace App\Composer;

use PhpParser\Node;

trait HasMergeClassMap
{
    /**
     * @param Node\Expr\ArrayItem[] $old
     * @param Node\Expr\ArrayItem[] $new
     */
    protected function mergeClassmap(array $old, array $new): array
    {
        foreach ($old as $key => $item) {
            foreach ($new as $newItem) {
                if ($item->key->value === $newItem->key->value) {
                    unset($old[$key]);
                }
            }
        }

        return array_merge($old, $new);
    }
}
