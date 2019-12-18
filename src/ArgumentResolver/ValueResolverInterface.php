<?php

namespace Bdf\Instantiator\ArgumentResolver;

use Bdf\Instantiator\ArgumentResolver\ArgumentMetadata\ArgumentMetadata;

/**
 * ValueResolverInterface
 */
interface ValueResolverInterface
{
    /**
     * Check if the resolver support this metadata
     *
     * @param ArgumentMetadata $metadata
     * @param int $i
     * @param array $parameters
     *
     * @return array
     */
    public function supports($metadata, $i, $parameters);

    /**
     * Returns the value of the argument
     *
     * @param ArgumentMetadata $metadata
     * @param int $i
     * @param array $parameters
     *
     * @return \Generator
     */
    public function resolve($metadata, $i, &$parameters);
}
