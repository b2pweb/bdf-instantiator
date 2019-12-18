<?php

namespace Bdf\Instantiator\ArgumentResolver\ValueResolver;

use Bdf\Instantiator\ArgumentResolver\ValueResolverInterface;

/**
 * Use the position to get the parameter from the given parameters
 */
class PositionedValue implements ValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($metadata, $i, $parameters)
    {
        if (!array_key_exists($i, $parameters)) {
            return false;
        }

        if (!$metadata->isClass()) {
            return true;
        }

        $className = $metadata->getType();

        return $parameters[$i] instanceof $className;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($metadata, $i, &$parameters)
    {
        if ($metadata->isVariadic()) {
            foreach ($parameters[$i] as $value) {
                yield $value;
            }
        } else {
            yield $parameters[$i];
        }
    }
}
