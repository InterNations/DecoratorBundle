<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Integration;

use InterNations\Bundle\DecoratorBundle\DependencyInjection\Compiler\Decorator\DecoratorCompilerPass;
use InterNations\Component\Testing\AbstractTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @group integration
 * @group large
 */
class DecoratorCompilerPassTest extends AbstractTestCase
{
    public function testExceptionIsThrownIfNoIdIsSetInDecorateThis()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\UnexpectedValueException',
            'Missing parameter "decorator_id" in tag named "decorator.decorate_this" for service "decorated"'
        );
        $this->createContainer('decorate-this-missing-id.xml');
    }

    public function testExceptionIsThrownIfNoWrappedDeclarationIsFoundInDecorateThis()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\InvalidArgumentException',
            'Decorator service "decorator" does not declare a constructor argument of type "service" with name "__subject__"'
        );
        $this->createContainer('decorate-this-missing-subject.xml');
    }

    public function testSingleDecorateThis()
    {
        $container = $this->createContainer('single-decorate-this.xml');

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDoubleDecorateThis()
    {
        $container = $this->createContainer('double-decorate-this.xml');

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator2', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()
        );
        $this->assertSame(
            'decorator1',
            $decorated->getWrapped()->getName()
        );

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testTripleDecorateThis()
    {
        $container = $this->createContainer('triple-decorate-this-with-priority.xml');

        $decorated = $container->get('decorated');

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator3', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()
        );
        $this->assertSame('decorator2', $decorated->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()->getWrapped()
        );
        $this->assertSame('decorator1', $decorated->getWrapped()->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()->getWrapped()->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getWrapped()->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testExceptionIsThrownIfNoIdIsSetInDecorateOther()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\UnexpectedValueException',
            'Missing parameter "service_id" in tag named "decorator.decorate_other" for service "decorated"'
        );
        $this->createContainer('decorate-other-missing-id.xml');
    }

    public function testExceptionIsThrownIfNoWrappedDeclarationIsFoundInDecorateOther()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\InvalidArgumentException',
            'Decorator service "decorator" does not declare a constructor argument of type "service" with name "__subject__"'
        );
        $this->createContainer('decorate-this-missing-service.xml');
    }

    public function testSingleDecorateOther()
    {
        $container = $this->createContainer('single-decorate-other.xml');

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateDecoratedDefinition()
    {
        $container = $this->createContainer('decorate-decorated-definition.xml');

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDoubleDecorateOther()
    {
        $container = $this->createContainer('double-decorate-other.xml');

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator2', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()
        );
        $this->assertSame(
            'decorator1',
            $decorated->getWrapped()->getName()
        );

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testTripleDecorateOther()
    {
        $container = $this->createContainer('triple-decorate-other-with-priority.xml');

        $decorated = $container->get('decorated');

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator3', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()
        );
        $this->assertSame('decorator2', $decorated->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()->getWrapped()
        );
        $this->assertSame('decorator1', $decorated->getWrapped()->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()->getWrapped()->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getWrapped()->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateCombined()
    {
        $container = $this->createContainer('decorate-this-and-other.xml');

        $decorated = $container->get('decorated');

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated
        );
        $this->assertSame('decorator1', $decorated->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()
        );
        $this->assertSame('decorator2', $decorated->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()->getWrapped()
        );
        $this->assertSame('decorator3', $decorated->getWrapped()->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator',
            $decorated->getWrapped()->getWrapped()->getWrapped()
        );
        $this->assertSame('decorator4', $decorated->getWrapped()->getWrapped()->getWrapped()->getName());

        $this->assertInstanceOf(
            'InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated',
            $decorated->getWrapped()->getWrapped()->getWrapped()->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getWrapped()->getWrapped()->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testExceptionIfNoSharedSupertype()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\UnexpectedValueException',
            '"SplFileInfo" ("decorator") is configured as a decorator for "Iterator" ("decorated") but do not share a common supertype. Implement a shared interface (e.g. by introducing "IteratorInterface") or extend a shared baseclass'
        );

        $this->createContainer('decorate-no-shared-supertype.xml');
    }

    public function testExceptionIfInvalidConstructorSignature()
    {
        $this->setExpectedException(
            'InterNations\Bundle\DecoratorBundle\Exception\InvalidArgumentException',
            'No constructor defined for class "stdClass"'
        );

        $this->createContainer('decorate-invalid-constructor.xml');
    }

    public function testDecorateInternalClasses()
    {
        $container = $this->createContainer('decorate-internal-classes.xml');

        $this->assertInstanceOf('IteratorIterator', $container->get('decorated'));
    }

    /**
     * @param string $file
     * @return ContainerInterface
     */
    public function createContainer($file)
    {
        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load($file);

        $compiler = new Compiler();
        $compiler->addPass(new DecoratorDefinitionCompilerPass());
        $compiler->addPass(new DecoratorCompilerPass());
        $compiler->compile($container);

        $dumper = new PhpDumper($container);
        $className = 'DecoratorTestServiceContainer' . rand();
            $source = $dumper->dump(['class' => $className]);

        $source = str_replace('<?php', '', $source);
        eval($source);

        return new $className();
    }
}

class DecoratorDefinitionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('decorated_original')) {
            $definition = new DefinitionDecorator('decorated_original');
            $definition->setProperty('definitionDecorator', true);
            $container->setDefinition('decorated', $definition);
        }
    }
}
