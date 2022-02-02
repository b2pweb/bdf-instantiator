<?php

namespace Bdf\Instantiator\ArgumentResolver\ArgumentMetadata;

use Bdf\Instantiator\Exception\InvalidCallableException;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
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
    public function createArgumentMetadata($callable): array
    {
        $arguments = [];

        try {
            $reflection = $this->getReflection($callable);
        } catch(ReflectionException $exception) {
            throw new InvalidCallableException($exception->getMessage(), $exception->getCode(), $exception);
        }

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();

            $arguments[] = new ArgumentMetadata(
                $param->getName(),
                $this->getType($type, $reflection),
                $param->isVariadic(),
                $param->isDefaultValueAvailable(),
                $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                $param->allowsNull(),
                $type instanceof ReflectionNamedType && !$type->isBuiltin()
            );
        }

        return $arguments;
    }

    /**
     * Gets the reflection of the callable.
     *
     * @param callable|object $callable
     *
     * @return ReflectionFunctionAbstract
     *
     * @throws ReflectionException
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
     * @param ReflectionType|null $type
     * @param ReflectionFunctionAbstract $function
     *
     * @return null|string
     */
    private function getType(?ReflectionType $type, ReflectionFunctionAbstract $function)
    {
        if (!$type instanceof ReflectionNamedType) {
            return null;
        }

        switch ($name = $type->getName()) {
            case 'self':
                return $function->getDeclaringClass()->name;

            case 'parent':
                return $function->getDeclaringClass()->getParentClass()->name;

            default:
                return $name;
        }
    }
}
