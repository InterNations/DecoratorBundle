<?php
namespace InterNations\Bundle\DecoratorBundle;

use InterNations\Bundle\DecoratorBundle\DependencyInjection\Compiler\Decorator\DecoratorCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InterNationsDecoratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DecoratorCompilerPass());
    }
}
