<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Tagger;

use InterNations\Bundle\DecoratorBundle\Tagger\DecorationTagger;
use InterNations\Component\Testing\AbstractTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DecorationTaggerTest extends AbstractTestCase
{
    /** @var ContainerBuilder|MockObject */
    private $container;

    /** @var Definition|MockObject */
    private $definition;

    public function setUp()
    {
        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->definition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->container
            ->expects($this->any())
            ->method('getDefinition')
            ->with('service_id')
            ->will($this->returnValue($this->definition));
    }

    public function testTagService()
    {
        $this->definition
            ->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue([]));

        $this->definition
            ->expects($this->once())
            ->method('setTags')
            ->with(['decorator.decorate_this' => [['decorator_id' => 'decorator_service_id', 'priority' => 0]]]);

        DecorationTagger::tag($this->container, 'service_id', 'decorator_service_id');
    }

    public function testTagServiceWithPriority()
    {
        $this->definition
            ->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue([]));

        $this->definition
            ->expects($this->once())
            ->method('setTags')
            ->with(['decorator.decorate_this' => [['decorator_id' => 'decorator_service_id', 'priority' => 255]]]);

        DecorationTagger::tag($this->container, 'service_id', 'decorator_service_id', 255);
    }

    public function testTagServiceTwice()
    {
        $this->definition
            ->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue(['decorator.decorate_this' => [['decorator_id' => 'other_decorator_service_id', 'priority' => 0]]]));

        $this->definition
            ->expects($this->once())
            ->method('setTags')
            ->with(
                [
                    'decorator.decorate_this' => [
                        ['decorator_id' => 'other_decorator_service_id', 'priority' => 0],
                        ['decorator_id' => 'decorator_service_id', 'priority' => 0],
                    ]
                ]
            );

        DecorationTagger::tag($this->container, 'service_id', 'decorator_service_id');
    }

    public function testTagInvalidService()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You have requested a non-existent service "service_id".');
        DecorationTagger::tag(new ContainerBuilder(), 'service_id', 'decorator_id');
    }
}
