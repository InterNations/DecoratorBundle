<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures;

class Decorated implements DecoratableInterface
{
    public function execute()
    {
        return __FUNCTION__;
    }

    public function getName()
    {
        return 'inner';
    }
}
