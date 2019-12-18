<?php

namespace Bdf\Instantiator\ArgumentResolver\ValueResolver;

use Bdf\Instantiator\ArgumentResolver\ValueResolverInterface;

/**
 * Use a name to get the parameter from the given parameters
 */
class NamedValue implements ValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($metadata, $i, $parameters)
    {
        if (!array_key_exists($metadata->getName(), $parameters)) {
            return false;
        }

        if (!$metadata->isClass()) {
            return true;
        }

        $className = $metadata->getType();

        return $parameters[$metadata->getName()] instanceof $className;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($metadata, $i, &$parameters)
    {
        if ($metadata->isVariadic()) {
            foreach ($parameters[$metadata->getName()] as $value) {
                yield $value;
            }
        } else {
            yield $parameters[$metadata->getName()];
        }
    }
}
