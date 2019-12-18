<?php

namespace Bdf\Instantiator\ArgumentResolver\ValueResolver;

use Bdf\Instantiator\ArgumentResolver\ValueResolverInterface;
use Bdf\Instantiator\InstantiatorInterface;

/**
 * Make a service from container.
 * Only if the parameter is a class / interface
 */
class ServiceValue implements ValueResolverInterface
{
    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    /**
     * ServiceValue constructor.
     *
     * @param InstantiatorInterface $instantiator
     */
    public function __construct(InstantiatorInterface $instantiator)
    {
        $this->instantiator = $instantiator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($metadata, $i, $parameters)
    {
        // We support here only complex type with no default value
        // or if constructor parameters has been set.
        return $metadata->isClass() && (!$metadata->hasDefaultValue() || isset($parameters[$metadata->getName()]));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($metadata, $i, &$parameters)
    {
        $params = $parameters[$metadata->getName()] ?? [];

        yield $this->instantiator->make($metadata->getType(), $params);
    }
}
