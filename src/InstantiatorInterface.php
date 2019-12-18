<?php

namespace Bdf\Instantiator;

/**
 * InstantiatorInterface
 *
 * @author Seb
 */
interface InstantiatorInterface
{
    /**
     * Create a callable from a string reprensentation
     *
     * <code>
     * $container->set('user', 'UserListener');
     *
     * // Add a classname and its method to call
     * // instanciate UserListener and call onEvent method
     * $instantiator->createCallable('UserListener@onEvent');
     *
     * // Add DI alias
     * // instanciate UserListener and call onEvent method
     * $instantiator->createCallable('user@onEvent');
     *
     * // Default method
     * // instanciate UserListener and call handle method
     * $instantiator->createCallable('UserListener');
     *
     * // Arguments resolve from DI
     * // instanciate UserListener with prime and mailer from DI
     * $instantiator->createCallable('user[prime, mailer]');
     *
     * // Arguments resolve from DI with command
     * // instanciate UserListener with self container and serviceId from DI
     * $instantiator->createCallable('user[Container, make:serviceId]');
     * </code>
     *
     * @param string $string  The string listener to resolve
     * @param string $method  The default method to call
     *
     * @return callable
     */
    public function createCallable($string, $method = 'handle');
    
    /**
     * Resolve the given type from the container.
     *
     * @param string  $id
     * @param mixed   $parameters
     *
     * @return mixed
     */
    public function make($id, $parameters = []);

    /**
     * Get all dependencies for a given method.
     *
     * @param callable|string $callback
     * @param array $parameters
     *
     * @return array
     */
    public function getMethodDependencies($callback, array $parameters = []);
}
