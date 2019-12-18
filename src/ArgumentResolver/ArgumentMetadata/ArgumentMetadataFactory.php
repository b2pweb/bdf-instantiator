<?php

namespace Bdf\Instantiator\ArgumentResolver\ArgumentMetadata;

use Bdf\Instantiator\Exception\InvalidCallableException;
use ReflectionParameter;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;

/**
 * Custom factory to manage additionnal metadata.
 * This code is provide by symfony 4
 *
 * @see \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory
 */
final class ArgumentMetadataFactory implements ArgumentMetadataFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createArgumentMetadata($callable)
    {
        $arguments = [];

        try {
            $reflection = $this->getReflection($callable);
        } catch(\ReflectionException $exception) {
            throw new InvalidCallableException($exception->getMessage(), $exception->getCode(), $exception);
        }

        foreach ($reflection->getParameters() as $param) {
            $arguments[] = new ArgumentMetadata(
                $param->getName(),
                $this->getType($param, $reflection),
                $param->isVariadic(),
                $param->isDefaultValueAvailable(),
                $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                $param->allowsNull(),
                $param->getClass() !== null
            );
        }

        return $arguments;
    }

    /**
     * Gets the reflection of the callable.
     *
     * @param callable|object $callable
     *
     * @return \ReflectionFunctionAbstract
     *
     * @throws \ReflectionException
     */
    private function getReflection($callable)
    {
        if (is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        if (is_object($callable) && !$callable instanceof \Closure) {
            return (new \ReflectionObject($callable))->getMethod('__invoke');
        }

        return new \ReflectionFunction($callable);
    }

    /**
     * Returns an associated type to the given parameter if available.
     *
     * @param ReflectionParameter $parameter
     * @param \ReflectionFunctionAbstract $function
     *
     * @return null|string
     */
    private function getType(ReflectionParameter $parameter, \ReflectionFunctionAbstract $function)
    {
        if (!$type = $parameter->getType()) {
            return null;
        }

        $name = $type->getName();

        if ('self' === $name) {
            return $function->getDeclaringClass()->name;
        }
        if ('parent' === $name) {
            return $function->getDeclaringClass()->getParentClass()->name;
        }

        return $name;
    }
}
