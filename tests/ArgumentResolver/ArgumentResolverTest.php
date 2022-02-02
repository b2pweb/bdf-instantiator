<?php

namespace Bdf\Instantiator\ArgumentResolver;

use _files\A;
use _files\Php8Syntax;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\DefaultValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\NamedValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\PositionedValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\ServiceValue;
use Bdf\Instantiator\Instantiator;
use League\Container\Container;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf
 * @group Bdf_DI
 * @group Bdf_DI_Instantiator
 */
class ArgumentResolverTest extends TestCase
{
    /**
     * 
     */
    public function test_not_found_from_string()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Callable "var_dump" requires that you provide a value for the "\$[a-z]+" argument./');

        $resolver = new ArgumentResolver();
        $resolver->getArguments('var_dump');
    }

    /**
     *
     */
    public function test_not_found_from_object()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "'.Foo::class.'" requires that you provide a value for the "$bar" argument.');

        $resolver = new ArgumentResolver();
        $resolver->getArguments(new Foo);
    }

    /**
     *
     */
    public function test_not_found_from_array_string()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "'.Foo::class.'::__invoke()" requires that you provide a value for the "$bar" argument.');

        $resolver = new ArgumentResolver();
        $resolver->getArguments([Foo::class, '__invoke']);
    }

    /**
     *
     */
    public function test_not_found_from_array()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "'.Foo::class.'::__invoke()" requires that you provide a value for the "$bar" argument.');

        $resolver = new ArgumentResolver();
        $resolver->getArguments([new Foo, '__invoke']);
    }

    /**
     *
     */
    public function test_resolve()
    {
        $resolver = new ArgumentResolver(null, [new PositionedValue(), new DefaultValue()]);
        $args = $resolver->getArguments([new Foo, 'test'], ['foo']);

        $this->assertSame(['foo', null], $args);
    }

    /**
     *
     */
    public function test_resolve_variadic()
    {
        $resolver = new ArgumentResolver(null, [new PositionedValue()]);
        $args = $resolver->getArguments([new Foo, 'variadic'], [['foo', 'bar']]);

        $this->assertSame(['foo', 'bar'], $args);
    }

    /**
     *
     */
    public function test_resolve_variadic_from_named_params()
    {
        $resolver = new ArgumentResolver(null, [new NamedValue()]);
        $args = $resolver->getArguments([new Foo, 'variadic'], ['args' => ['foo', 'bar']]);

        $this->assertSame(['foo', 'bar'], $args);
    }

    /**
     * @requires PHP 8
     */
    public function test_ignore_typed_arguments_with_union_or_builtin_types()
    {
        require_once __DIR__ . '/../_files/Php8Syntax.php';

        $container = new Container();
        $instantiator = new Instantiator($container);

        $resolver = new ArgumentResolver(null, [
            new NamedValue(),
            new PositionedValue(),
            new ServiceValue($instantiator),
            new DefaultValue(),
        ]);

        $a = new A();
        $this->assertSame([$a], $resolver->getArguments([new Php8Syntax(), 'withUnion'], [$a]));

        $args = ['foo', new class { public function __invoke() {} }];
        $this->assertSame($args, $resolver->getArguments([new Php8Syntax(), 'withBuiltinType'], $args));
    }
}

class Foo
{
    public function __invoke($bar)
    {
    }

    public function test($foo, $bar = null)
    {
    }

    public function variadic(...$args)
    {
    }
}
