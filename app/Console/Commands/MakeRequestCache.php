<?php

namespace App\Console\Commands;

use App\Annotation\Validate;
use App\Composer\ClassCache;
use Composer\Autoload\ClassLoader;
use Illuminate\Console\Command;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class MakeRequestCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-request-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $classMap = $this->getClassLoader()->getClassMap();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $printer = new Standard();
        foreach ($classMap as $class => $file) {
            if (!str_starts_with($class, 'App\\Http\\Requests\\')) {
                continue;
            }

            $rulesMethod = new ClassMethod('rules', ['flags' => 1, 'returnType' => 'array']);
            $rulesMethod->stmts[] = new Return_($this->buildArrayNode($this->getRules($class)));

            $ast = $parser->parse(file_get_contents($file));
            $visitor = new class([$rulesMethod]) extends NodeVisitorAbstract {
                public function __construct(protected array $methods)
                {
                }

                public function enterNode(Node $node): void
                {
                    if ($node instanceof Class_) {
                        $node->stmts = array_merge($node->stmts, $this->methods);
                    }
                }
            };
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);

            $proxyCode = $printer->prettyPrintFile($traverser->traverse($ast));

            $classFile = str_replace('\\', '_', $class . '.cache.php');
            $cachePath = storage_path($classFile);

            file_put_contents($cachePath, $proxyCode);

            $this->output->info("生成缓存文件：{$cachePath}");
            $classMap[$class] = $cachePath;
        }
        ClassCache::reloadClassMap($classMap);
    }

    private function getRules(string $class): array
    {
        $ref = new \ReflectionClass($class);

        foreach ($ref->getProperties() as $property) {
            $attributes = $property->getAttributes(Validate::class);
            if (empty($attributes[0])) {
                continue;
            }
            /** @var Validate $validate */
            $validate = $attributes[0]->newInstance();

            $rules[$property->getName()] = $validate->getRules();
        }
        return $rules ?? [];
    }

    private function getClassLoader(): ClassLoader
    {
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            if (is_array($loader) && $loader[0] instanceof ClassLoader) {
                return $loader[0];
            }
        }

        throw new \Exception('Composer loader not found.');
    }

    public function buildArrayNode(array $array): Array_
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
            'array' => $this->buildArrayNode($value),
            default => null
        };
    }
}
