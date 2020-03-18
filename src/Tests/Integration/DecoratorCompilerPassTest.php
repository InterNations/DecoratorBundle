<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Integration;

use InterNations\Bundle\DecoratorBundle\DependencyInjection\Compiler\Decorator\DecoratorCompilerPass;
use InterNations\Bundle\DecoratorBundle\Exception\InvalidArgumentException;
use InterNations\Bundle\DecoratorBundle\Exception\UnexpectedValueException;
use InterNations\Bundle\DecoratorBundle\Tagger\DecorationTagger;
use InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\DecoratableInterface;
use InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorated;
use InterNations\Bundle\DecoratorBundle\Tests\Integration\Fixtures\Decorator;
use InterNations\Component\Testing\AbstractTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Missing parameter "decorator_id" in tag named "decorator.decorate_this" for service "decorated"'
        );
        $this->createContainer('decorate-this-missing-id.xml');
    }

    public function testExceptionIsThrownIfNoWrappedDeclarationIsFoundInDecorateThis()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Decorator service "decorator" does not declare a constructor argument of type "service" with name "__subject__"'
        );
        $this->createContainer('decorate-this-missing-subject.xml');
    }

    public function testSingleDecorateThis()
    {
        $container = $this->createContainer('single-decorate-this.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator', 0, $decorated);
        $this->assertUnwrappedDecorator('inner', 1, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDoubleDecorateThis()
    {
        $container = $this->createContainer('double-decorate-this.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator2', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator1', 1, $decorated);
        $this->assertUnwrappedDecorator('inner', 2, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testTripleDecorateThis()
    {
        $container = $this->createContainer('triple-decorate-this-with-priority.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator3', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('decorator1', 2, $decorated);
        $this->assertUnwrappedDecorator('inner', 3, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testExceptionIsThrownIfNoIdIsSetInDecorateOther()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Missing parameter "service_id" in tag named "decorator.decorate_other" for service "decorated"'
        );
        $this->createContainer('decorate-other-missing-id.xml');
    }

    public function testExceptionIsThrownIfNoWrappedDeclarationIsFoundInDecorateOther()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Decorator service "decorator" does not declare a constructor argument of type "service" with name "__subject__"'
        );
        $this->createContainer('decorate-this-missing-service.xml');
    }

    public function testSingleDecorateOther()
    {
        $container = $this->createContainer('single-decorate-other.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator', 0, $decorated);
        $this->assertUnwrappedDecorator('inner', 1, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateDecoratedDefinition()
    {
        $container = $this->createContainer('decorate-decorated-definition.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator', 0, $decorated);
        $this->assertUnwrappedDecorator('inner', 1, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDoubleDecorateOther()
    {
        $container = $this->createContainer('double-decorate-other.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator2', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator1', 1, $decorated);
        $this->assertUnwrappedDecorator('inner', 2, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testTripleDecorateOther()
    {
        $container = $this->createContainer('triple-decorate-other-with-priority.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator3', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('decorator1', 2, $decorated);
        $this->assertUnwrappedDecorator('inner', 3, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateCombined()
    {
        $container = $this->createContainer('decorate-this-and-other.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator1', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('decorator3', 2, $decorated);
        $this->assertUnwrappedDecorator('decorator4', 3, $decorated);
        $this->assertUnwrappedDecorator('inner', 4, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateTenTimes()
    {
        $container = $this->createContainer('decorate-other-ten.xml');

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator1', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('decorator3', 2, $decorated);
        $this->assertUnwrappedDecorator('decorator4', 3, $decorated);
        $this->assertUnwrappedDecorator('decorator5', 4, $decorated);
        $this->assertUnwrappedDecorator('decorator6', 5, $decorated);
        $this->assertUnwrappedDecorator('decorator7', 6, $decorated);
        $this->assertUnwrappedDecorator('decorator8', 7, $decorated);
        $this->assertUnwrappedDecorator('decorator9', 8, $decorated);
        $this->assertUnwrappedDecorator('decorator10', 9, $decorated);
        $this->assertUnwrappedDecorator('inner', 10, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testExceptionIfNoSharedSupertype()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            '"SplFileInfo" ("decorator") is configured as a decorator for "Iterator" ("decorated") but do not share a common supertype. Implement a shared interface (e.g. by introducing "IteratorInterface") or extend a shared baseclass'
        );

        $this->createContainer('decorate-no-shared-supertype.xml');
    }

    public function testExceptionIfInvalidConstructorSignature()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No constructor defined for class "stdClass"');

        $this->createContainer('decorate-invalid-constructor.xml');
    }

    public function testDecorateInternalClasses()
    {
        $container = $this->createContainer('decorate-internal-classes.xml');

        $this->assertInstanceOf('IteratorIterator', $container->get('decorated'));
    }

    public function testDecorateProgrammaticallySingle()
    {
        $container = $this->createContainer(
            'decorate-programmatically.xml',
            static function (ContainerBuilder $container) {
                DecorationTagger::tag($container, 'decorated', 'decorator1');
            }
        );

        $decorated = $container->get('decorated');
        $this->assertInstanceOf(
            Decorator::class,
            $decorated
        );
        $this->assertSame('decorator1', $decorated->getName());

        $this->assertInstanceOf(
            Decorated::class,
            $decorated->getWrapped()
        );
        $this->assertSame('inner', $decorated->getWrapped()->getName());

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateProgrammaticallyTwice()
    {
        $container = $this->createContainer(
            'decorate-programmatically.xml',
            static function (ContainerBuilder $container) {
                DecorationTagger::tag($container, 'decorated', 'decorator1');
                DecorationTagger::tag($container, 'decorated', 'decorator2');
            }
        );

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator1', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('inner', 2, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateProgrammaticallyTwiceWithPriority()
    {
        $container = $this->createContainer(
            'decorate-programmatically.xml',
            static function (ContainerBuilder $container) {
                DecorationTagger::tag($container, 'decorated', 'decorator2', 2);
                DecorationTagger::tag($container, 'decorated', 'decorator1', 1);
            }
        );

        $decorated = $container->get('decorated');

        $this->assertUnwrappedDecorator('decorator2', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator1', 1, $decorated);
        $this->assertUnwrappedDecorator('inner', 2, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    public function testDecorateProgrammaticallyAndDeclarativeWithPriority()
    {
        $container = $this->createContainer(
            'decorate-programmatically.xml',
            static function (ContainerBuilder $container) {
                DecorationTagger::tag($container, 'decorated2', 'decorator3', -1);
                DecorationTagger::tag($container, 'decorated2', 'decorator1', 1);
            }
        );

        $decorated = $container->get('decorated2');

        $this->assertUnwrappedDecorator('decorator1', 0, $decorated);
        $this->assertUnwrappedDecorator('decorator2', 1, $decorated);
        $this->assertUnwrappedDecorator('decorator3', 2, $decorated);
        $this->assertUnwrappedDecorator('inner', 3, $decorated, Decorated::class);

        $this->assertSame('execute', $decorated->execute());
    }

    /**
     * @param string $file
     * @param callable $pass
     * @return ContainerInterface
     */
    public function createContainer($file, callable $pass = null)
    {
        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load($file);

        if ($pass) {
            $pass($container);
        }

        $compiler = new Compiler();
        $compiler->addPass(new DecoratorDefinitionCompilerPass());
        $compiler->addPass(new DecoratorCompilerPass());
        $compiler->compile($container);

        if (!$container->isCompiled()) {
            $container->compile();
        }

        $dumper = new PhpDumper($container);
        $className = 'DecoratorTestServiceContainer' . rand();
            $source = $dumper->dump(['class' => $className]);

        $source = str_replace('<?php', '', $source);
        eval($source);

        return new $className();
    }

    private function assertUnwrappedDecorator(
        $expectedName,
        $times,
        DecoratableInterface $object,
        $expectedClass = Decorator::class
    )
    {
        for ($a = 0; $a < $times; $a++) {
            $this->assertInstanceOf(
                Decorator::class,
                $object,
                sprintf('Expected "%s" at level %d', Decorator::class, $a - 1)
            );
            $object = $object->getWrapped();
        }

        $this->assertInstanceOf(
            $expectedClass,
            $object,
            sprintf('Expected "%s" at level %d', Decorator::class, $times)
        );
        $this->assertSame($expectedName, $object->getName(), 'At level ' . $times);
    }
}

class DecoratorDefinitionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('decorated_original')) {
            $definition = new ChildDefinition('decorated_original');
            $definition->setProperty('definitionDecorator', true);
            $container->setDefinition('decorated', $definition);
        }
    }
}
