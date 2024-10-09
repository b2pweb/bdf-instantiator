<?php

namespace Bdf\Instantiator;

use Bdf\Instantiator\ArgumentResolver\ArgumentResolver;
use Bdf\Instantiator\ArgumentResolver\ArgumentResolverInterface;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\DefaultValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\NamedValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\PositionedValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolver\ServiceValue;
use Bdf\Instantiator\ArgumentResolver\ValueResolverInterface;
use Bdf\Instantiator\Exception\ClassNotExistsException;
use Bdf\Instantiator\Exception\InvalidCallableException;
use Closure;
use Psr\Container\ContainerInterface;

/**
 * DIInstantiator
 * 
 * Instantiate object with their dependencies
 *
 * @author Seb
 */
class Instantiator implements InstantiatorInterface
{
    /**
     * The application container
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * The arguments resolver
     *
     * @var ArgumentResolverInterface
     */
    private $arguments;

    /**
     * The resolver for argument resolver
     * Should return ArgumentResolverInterface
     *
     * @var Closure|null
     */
    private $argumentsResolver;

    /**
     * Constructor
     * 
     * @param ContainerInterface $container
     * @param null|Closure $argumentsResolver
     */
    public function __construct(ContainerInterface $container, ?Closure $argumentsResolver = null)
    {
        $this->container = $container;
        $this->argumentsResolver = $argumentsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function createCallable($string, $method = '__invoke')
    {
        $className = $string;
        $arguments = [];

        if (strpos($string, '@') !== false) {
            list($className, $method) = explode('@', $string, 2);
        }

        if (($start = strpos($string, '[')) !== false) {
            $className = substr($string, 0, $start);
            $pattern = substr($string, $start +1, strpos($string, ']') - $start -1);

            foreach (explode(',', $pattern) as $id) {
                $arguments[] = $this->make(trim($id));
            }
        }

        return [$this->make($className, $arguments), $method];
    }

    /**
     * {@inheritdoc}
     */
    public function make($id, $parameters = [])
    {
        if ($this->container->has($id)) {
            $id = $this->container->get($id);

            if (!is_string($id) && !$id instanceof Closure) {
                return $id;
            }
        }

        return $this->instantiate($id, $parameters);
    }

    /**
     * Instantiate a class with parameters
     *
     * @param mixed   $class
     * @param mixed   $parameters
     *
     * @return object
     */
    private function instantiate($class, $parameters = [])
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if ($class instanceof Closure) {
            // TODO verifier que parameters est bien un tableau numérique ?
            return $class(...$parameters);
        }

        try {
            $arguments = $this->getMethodDependencies([$class, '__construct'], $parameters);
        } catch (InvalidCallableException $exception) {
            // Method constructor not found
            $arguments = [];

            // The invalid callable can be thrown by a invalid class.
            if (!class_exists($class)) {
                throw new ClassNotExistsException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        return new $class(...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodDependencies($callback, array $parameters = [])
    {
        return $this->getArgumentResolver()->getArguments($callback, $parameters);
    }

    /**
     * Set the argument resolver
     *
     * @param ArgumentResolverInterface $arguments
     */
    public function setArgumentResolver(ArgumentResolverInterface $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Get the argument resolver
     *
     * @return ArgumentResolverInterface
     */
    public function getArgumentResolver(): ArgumentResolverInterface
    {
        if ($this->arguments === null) {
            $this->resolveArgumentResolver();
        }

        return $this->arguments;
    }

    /**
     * Instantiate the argument resolver from the resolver
     */
    private function resolveArgumentResolver(): void
    {
        if ($this->argumentsResolver === null) {
            $this->arguments = new ArgumentResolver(null, $this->getDefaultValueResolver());
        } else {
            $resolver = $this->argumentsResolver;
            $this->arguments = $resolver();
            $this->argumentsResolver = null;
        }
    }

    /**
     * Gets the default value resolvers
     *
     * @return ValueResolverInterface[]
     */
    public function getDefaultValueResolver(): array
    {
        return [
            new NamedValue(),
            new PositionedValue(),
            new ServiceValue($this),
            new DefaultValue(),
        ];
    }
}
