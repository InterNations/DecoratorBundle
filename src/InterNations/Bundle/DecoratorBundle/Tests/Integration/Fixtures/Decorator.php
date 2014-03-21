<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures;

class Decorator implements DecoratableInterface
{
    private $name;

    private $wrapped;

    public function __construct($name, DecoratableInterface $wrapped)
    {
        $this->name = $name;
        $this->wrapped = $wrapped;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWrapped()
    {
        return $this->wrapped;
    }

    public function execute()
    {
        return $this->wrapped->execute();
    }
}
