<?php

namespace Bdf\Instantiator\ArgumentResolver\ValueResolver;

use Bdf\Instantiator\ArgumentResolver\ValueResolverInterface;

/**
 * Get the default value
 *
 * If the parameter has a default value OR accepts null
 */
class DefaultValue implements ValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($metadata, $i, $parameters)
    {
        return $metadata->hasDefaultValue()
            || ($metadata->getType() !== null && $metadata->isNullable() && !$metadata->isVariadic());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($metadata, $i, &$parameters)
    {
        yield $metadata->hasDefaultValue() ? $metadata->getDefaultValue() : null;
    }
}
