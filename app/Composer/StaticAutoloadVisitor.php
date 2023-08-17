<?php


namespace App\Composer;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Comment;

class StaticAutoloadVisitor extends NodeVisitorAbstract
{
    use HasMergeClassMap;

    public function __construct(protected ArrayNodeBuilder $arrayNodeBuilder, protected array $classmap)
    {
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Property && $node->props[0]->name->name === 'classMap') {
            /** @var Node\Stmt\PropertyProperty $propertyProperty */
            $propertyProperty = $node->props[0];
            $propertyProperty->default->items = $this->mergeClassmap($propertyProperty->default->items, $this->arrayNodeBuilder->build($this->classmap)->items);
            /** @var Node\Expr\ArrayItem $item */
            foreach ($propertyProperty->default->items as $item) {
                $item->setDocComment(new Comment\Doc("\n"));
            }
        }

    }
}
