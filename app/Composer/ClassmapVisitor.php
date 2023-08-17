<?php


namespace App\Composer;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Comment;

class ClassmapVisitor extends NodeVisitorAbstract
{
    use HasMergeClassMap;

    public function __construct(protected ArrayNodeBuilder $arrayNodeBuilder, protected array $classmap)
    {
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Return_) {
            /** @var Node\Expr\Array_ $expr */
            $expr = $node->expr;
            $expr->items = $this->mergeClassmap($expr->items, $this->arrayNodeBuilder->build($this->classmap)->items);
            /** @var Node\Expr\ArrayItem $item */
            foreach ($expr->items as $item) {
                $item->setDocComment(new Comment\Doc("\n"));
            }
            $node->expr = $expr;
        }
    }
}
