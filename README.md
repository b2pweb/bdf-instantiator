# B2PWeb Instantiator

A basic object instantiator.

[![build](https://github.com/b2pweb/bdf-instantiator/actions/workflows/php.yml/badge.svg)](https://github.com/b2pweb/bdf-instantiator/actions/workflows/php.yml)
[![codecov](https://codecov.io/github/b2pweb/bdf-instantiator/branch/master/graph/badge.svg?token=VOFSPEWYKX)](https://app.codecov.io/github/b2pweb/bdf-instantiator)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-instantiator.svg)](https://packagist.org/packages/b2pweb/bdf-instantiator)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-instantiator.svg)](https://packagist.org/packages/b2pweb/bdf-instantiator)

## Install via composer
```bash
$ composer require b2pweb/bdf-instantiator
```

## Usage Instruction

Basic usage when resolving from container definition.

```PHP
use Bdf\Instantiator\Instantiator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/** @var ContainerInterface $container */
$container->add(Logger::class, LoggerInterface::class);

$instantiator = new Instantiator($container);
$instantiator->make(LoggerInterface::class);
```

The instantiator resolve the dependencies of a method based on container definitions.

```PHP
use Bdf\Instantiator\Instantiator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Foo
{
    public $logger;
    
    /**
     * Foo constructor.
     * 
     * @param LoggerInterface $logger  
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

/** @var ContainerInterface $container */
$container->add(Logger::class, LoggerInterface::class);

$instantiator = new Instantiator($container);
$foo = $instantiator->make(Foo::class);

var_dump($foo->logger); // Logger
```

## License

Distributed under the terms of the MIT license.
