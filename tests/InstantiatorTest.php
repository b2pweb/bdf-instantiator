<?php

namespace Bdf\Instantiator;

use Bdf\Instantiator\ArgumentResolver\ArgumentResolver;
use Bdf\Instantiator\ArgumentResolver\ArgumentResolverInterface;
use Bdf\Instantiator\Exception\ClassNotExistsException;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group Bdf
 * @group Bdf_
 * @group Bdf_DI_Instantiator
 */
class InstantiatorTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Instantiator
     */
    private $instantiator;
    
    /**
     * 
     */
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->instantiator = new Instantiator($this->container);
    }
    
    /**
     * 
     */
    public function test_set_get_argument_resolver()
    {
        $resolver = $this->createMock(ArgumentResolverInterface::class);

        $this->instantiator->setArgumentResolver($resolver);

        $this->assertSame($resolver, $this->instantiator->getArgumentResolver());
    }

    /**
     *
     */
    public function test_default_argument_resolver()
    {
        $this->assertInstanceOf(ArgumentResolver::class, $this->instantiator->getArgumentResolver());
    }

    /**
     *
     */
    public function test_resolve_argument_resolver()
    {
        $expected = null;

        $this->instantiator = new Instantiator($this->container, function() use(&$expected) {
            $expected = $this->createMock(ArgumentResolverInterface::class);

            return $expected;
        });

        $resolver = $this->instantiator->getArgumentResolver();

        $this->assertSame($expected, $resolver);
    }

    /**
     *
     */
    public function test_at_synthax()
    {
        $this->container->add('test', new stdClass());

        $resolved = $this->instantiator->createCallable('test@method');

        $this->assertEquals(new stdClass(), $resolved[0]);
        $this->assertEquals('method', $resolved[1]);
    }

    /**
     * 
     */
    public function test_without_method()
    {
        $this->container->add('test', new stdClass());
        
        $resolved = $this->instantiator->createCallable('test');
        
        $this->assertEquals(new stdClass(), $resolved[0]);
        $this->assertEquals('__invoke', $resolved[1]);
    }
    
    /**
     * 
     */
    public function test_with_arguments()
    {
        $this->container->add('test1', 1);
        $this->container->add('test2', 2);
        
        $resolved = $this->instantiator->createCallable(TestInstanciatorListener::class.'[test1, test2]@method');
        
        $this->assertEquals([1, 2], $resolved[0]->args);
    }
    
    /**
     * 
     */
    public function test_command()
    {
        $this->container->add('class', $object = new stdClass);
        
        $resolved = $this->instantiator->createCallable(TestInstanciatorMakeCommand::class.'[class]@method');
        
        $this->assertEquals($object, $resolved[0]->make);
    }

    /**
     *
     */
    public function test_make_simple_method()
    {
        $callback = function($foo, $bar) {
            return func_get_args();
        };

        $args = $this->instantiator->getMethodDependencies($callback, ['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $callback(...$args));
    }

    /**
     *
     */
    public function test_named_parameter_method()
    {
        $callback = function($foo, $bar = null, $other = 0) {
            return func_get_args();
        };

        $args = $this->instantiator->getMethodDependencies($callback, ['foo' => 'foo', 'bar' => 'bar']);

        $this->assertSame(['foo', 'bar', 0], $callback(...$args));
    }

    /**
     *
     */
    public function test_make_method_with_complex_arg()
    {
        $callback = function(TestInstanciatorMakeMethod $foo) {
            return func_get_args();
        };

        $args = $this->instantiator->getMethodDependencies($callback);

        $this->assertEquals([new TestInstanciatorMakeMethod], $callback(...$args));
    }

    /**
     *
     */
    public function test_array_callable()
    {
        $object = new TestInstanciatorMakeMethod();

        $args = $this->instantiator->getMethodDependencies([$object, 'method'], ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $object->method(...$args));
    }

    /**
     *
     */
    public function test_array_callable2()
    {
        $object = new TestInstanciatorMakeMethod();

        $args = $this->instantiator->getMethodDependencies([TestInstanciatorMakeMethod::class, 'method'], ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $object->method(...$args));
    }

    /**
     *
     */
    public function test_parameter_value_missing()
    {
        $this->expectException(\RuntimeException::class);

        $this->instantiator->getMethodDependencies([TestInstanciatorMakeMethod::class, 'method']);
    }

    /**
     *
     */
    public function test_make_existing_item()
    {
        $this->container->add('item', ['test']);

        $this->assertSame(['test'], $this->instantiator->make('item'));
    }

    /**
     *
     */
    public function test_make_existing_object()
    {
        $this->container->add('object', new InstanciatorDependencyObject('test'));

        $this->assertSame('test', $this->instantiator->make('object')->value);
    }

    /**
     *
     */
    public function test_make_object()
    {
        $this->assertInstanceOf(InstanciatorNullObject::class, $this->instantiator->make(InstanciatorNullObject::class));
    }

    /**
     *
     */
    public function test_make_object_by_key()
    {
        $this->container->add(InstanciatorNullObject::class);

        $this->assertInstanceOf(InstanciatorNullObject::class, $this->instantiator->make(InstanciatorNullObject::class));
    }

    /**
     *
     */
    public function test_make_object_by_alias()
    {
        $this->container->add('object', InstanciatorNullObject::class);

        $this->assertInstanceOf(InstanciatorNullObject::class, $this->instantiator->make('object'));
    }

    /**
     *
     */
    public function test_make_object_without_parameters()
    {
        $result = $this->instantiator->make(InstanciatorMakeObject::class);

        $this->assertInstanceOf(InstanciatorMakeObject::class, $result);
        $this->assertSame(null, $result->object);
    }

    /**
     *
     */
    public function test_make_object_with_dependencies_object()
    {
        $result = $this->instantiator->make(InstanciatorMakeObject::class, [
            'object' => new InstanciatorDependencyObject('test'),
        ]);

        $this->assertInstanceOf(InstanciatorMakeObject::class, $result);
        $this->assertSame('test', $result->object->value);
    }

    /**
     * @group test
     */
    public function test_make_object_with_dependencies_scalar()
    {
        $result = $this->instantiator->make(InstanciatorMakeObject::class, [
            'object' => 'test',
        ]);

        $this->assertInstanceOf(InstanciatorMakeObject::class, $result);
        $this->assertSame('test', $result->object->value);
    }

    /**
     *
     */
    public function test_make_object_with_dependencies_array()
    {
        $result = $this->instantiator->make(InstanciatorMakeObject::class, [
            'object' => ['value' => 'test'],
        ]);

        $this->assertInstanceOf(InstanciatorMakeObject::class, $result);
        $this->assertSame('test', $result->object->value);
    }

    /**
     *
     */
    public function test_make_object_with_not_given_dependencies_object_by_key()
    {
        $this->container->add(InstanciatorSelfDependencyObjectInterface::class, InstanciatorSelfDependencyObject::class);

        $result = $this->instantiator->make(InstanciatorSelfMakeObject::class);

        $this->assertInstanceOf(InstanciatorSelfMakeObject::class, $result);
        $this->assertInstanceOf(InstanciatorSelfDependencyObject::class, $result->object);
    }

    /**
     *
     */
    public function test_make_throws_require_dependencies()
    {
        $this->expectException(\RuntimeException::class);
        if (method_exists($this, 'expectExceptionMessageMatches')) {
            $this->expectExceptionMessageMatches('/requires that you provide a value for/');
        } else {
            $this->expectExceptionMessageRegExp('/requires that you provide a value for/');
        }

        $this->instantiator->make(InstanciatorDependencyObject::class);
    }

    /**
     *
     */
    public function test_make_invalid_class()
    {
        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage('Class Bdf\Instantiator\Unknown does not exist');

        $this->instantiator->make(Unknown::class);
    }
}

//----------------- test classes

class TestInstanciatorListener
{
    public $args;
    
    public function __construct($arg1, $arg2)
    {
        $this->args = [$arg1, $arg2];
    }
    
    public function method()
    {
    }
}

class TestInstanciatorMakeCommand
{
    public $make;
    
    public function __construct($make)
    {
        $this->make = $make;
    }
    public function method()
    {
    }
}
class TestInstanciatorMakeMethod
{
    public function method($foo, $bar)
    {
        return func_get_args();
    }
}


/**
 * test make
 */
class InstanciatorDependencyObject
{
    public $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
}
class InstanciatorMakeObject
{
    public $object;
    public function __construct(InstanciatorDependencyObject $object = null)
    {
        $this->object = $object;
    }
}
class InstanciatorNullObject
{
}
interface InstanciatorSelfDependencyObjectInterface
{
    public function depend();
}
class InstanciatorSelfDependencyObject implements InstanciatorSelfDependencyObjectInterface
{
    public function depend() {}
}
class InstanciatorSelfMakeObject
{
    public $object;
    public function __construct(InstanciatorSelfDependencyObjectInterface $object)
    {
        $this->object = $object;
    }
}