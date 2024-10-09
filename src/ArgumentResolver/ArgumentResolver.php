<?php

namespace Bdf\Instantiator\ArgumentResolver;

use Bdf\Instantiator\ArgumentResolver\ArgumentMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;

/**
 * ArgumentResolver
 *
 * This code is inspired by the class \Symfony\Component\HttpKernel\Controller\ArgumentResolver
 */
final class ArgumentResolver implements ArgumentResolverInterface
{
    /**
     * The argument metadata factory
     *
     * @var ArgumentMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * The default value resolvers
     *
     * @var ValueResolverInterface[]
     */
    private $resolvers;

    /**
     * ArgumentResolver constructor.
     *
     * @param ArgumentMetadataFactoryInterface|null $metadataFactory
     * @param ValueResolverInterface[] $resolvers
     */
    public function __construct(?ArgumentMetadataFactoryInterface $metadataFactory = null, array $resolvers = [])
    {
        $this->metadataFactory = $metadataFactory ?: new ArgumentMetadataFactory();
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments($callable, array $parameters = [])
    {
        $values = [];

        foreach ($this->metadataFactory->createArgumentMetadata($callable) as $i => $metadata) {
            foreach ($this->resolvers as $resolver) {
                if (!$resolver->supports($metadata, $i, $parameters)) {
                    continue;
                }

                foreach ($resolver->resolve($metadata, $i, $parameters) as $append) {
                    $values[] = $append;
                }

                // continue to the next argument
                continue 2;
            }

            $this->throwArgumentValueNotFound($callable, $metadata->getName());
        }

        return $values;
    }

    /**
     * Throws the exception
     *
     * @param callable $callable
     * @param string $argument
     *
     * @throws \RuntimeException
     */
    private function throwArgumentValueNotFound($callable, $argument)
    {
        if (is_array($callable)) {
            $class = $callable[0];

            if (!is_string($class)) {
                $class = get_class($class);
            }

            $representative = sprintf('%s::%s()', $class, $callable[1]);
        } elseif (is_object($callable)) {
            $representative = get_class($callable);
        } else {
            $representative = $callable;
        }

        throw new \RuntimeException(
            'Callable "'.$representative.'" requires that you provide a value for the "$'.$argument.'" argument.'
        );
    }
}
