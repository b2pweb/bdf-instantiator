<?php

namespace Bdf\Instantiator\ArgumentResolver\ArgumentMetadata;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata as BaseArgumentMetadata;

/**
 * ArgumentMetadata
 */
class ArgumentMetadata extends BaseArgumentMetadata
{
    /**
     * @var bool
     */
    private $isClass;

    /**
     * ArgumentMetadata constructor.
     *
     * @param string $name
     * @param null|string $type
     * @param bool $isVariadic
     * @param bool $hasDefaultValue
     * @param mixed $defaultValue
     * @param bool $isNullable
     * @param bool $isClass
     * @param object[] $attributes
     */
    public function __construct(string $name, ?string $type, bool $isVariadic, bool $hasDefaultValue, $defaultValue, bool $isNullable = false, bool $isClass = false, array $attributes = [])
    {
        parent::__construct(
            $name,
            $type,
            $isVariadic,
            $hasDefaultValue,
            $defaultValue,
            $isNullable,
            $attributes
        );

        $this->isClass = $isClass;
    }

    /**
     * Checks whether the parameter is a object
     *
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->isClass;
    }
}
