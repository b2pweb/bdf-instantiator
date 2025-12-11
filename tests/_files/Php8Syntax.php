<?php

namespace _files;

class Php8Syntax
{
    public function withUnion(A|B $b)
    {

    }

    public function withBuiltinType(string $foo, callable $bar)
    {

    }

    public function withAttribute(#[MyAttribute(42)] $param)
    {

    }
}

class A
{

}

class B
{

}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MyAttribute
{
    public function __construct(
        public int $value,
    ) {}
}
