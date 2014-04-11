<?php
namespace InterNations\Bundle\DecoratorBundle\Tagger;

use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DecorationTagger
{
    const TAG = 'decorator.decorate_this';

    public static function tag(ContainerBuilder $container, $serviceId, $decoratorServiceId, $priority = 0)
    {
        $definition = $container->getDefinition($serviceId);

        $data = ['decorator_id' => $decoratorServiceId, 'priority' => $priority];

        $definition->setTags(array_merge_recursive($definition->getTags(), [static::TAG => [$data]]));
    }
}
