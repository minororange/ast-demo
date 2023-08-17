<?php

namespace App\Composer;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class ClassCache
{
    private static function reload(string $classMapPath, NodeVisitorAbstract $visitor): void
    {
        echo "reload {$classMapPath} start\n";
        $printer = new Standard();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse(file_get_contents($classMapPath));

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);

        $proxy = $traverser->traverse($stmts);

        file_put_contents($classMapPath, $printer->prettyPrintFile($proxy));
        echo "reload {$classMapPath} end\n";
    }

    public static function reloadClassMap(array $classmap): void
    {
        if (empty($classmap)) {
            return;
        }
        $arrayNodeBuilder = new ArrayNodeBuilder();

        self::reload(
            './vendor/composer/autoload_classmap.php',
            new ClassmapVisitor($arrayNodeBuilder, $classmap)
        );

        self::reload(
            './vendor/composer/autoload_static.php',
            new StaticAutoloadVisitor($arrayNodeBuilder, $classmap)
        );
    }


}
