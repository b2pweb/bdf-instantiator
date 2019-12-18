<?php

namespace Bdf\Instantiator\ArgumentResolver;

/**
 *
 */
interface ArgumentResolverInterface
{
    /**
     * Returns the arguments to pass to the controller.
     *
     * @param callable $callable
     * @param array $parameters  The parameter to pass
     *
     * @return array The arguments value to pass to the callable
     *
     * @throws \RuntimeException When no value could be provided for a required argument
     */
    public function getArguments($callable, array $parameters = []);
}
