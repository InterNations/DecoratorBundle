<?php
namespace InterNations\Bundle\DecoratorBundle\DependencyInjection\Compiler\Decorator;

use InterNations\Bundle\DecoratorBundle\Exception\UnexpectedValueException;
use InterNations\Bundle\DecoratorBundle\Exception\InvalidArgumentException;
use InterNations\Bundle\DecoratorBundle\Inspector\DecoratorInspector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Util\SecureRandom;

class DecoratorCompilerPass implements CompilerPassInterface
{
    /** @var SecureRandom */
    private $secureRandom;

    /** @var DecoratorInspector */
    private $inspector;

    public function __construct()
    {
        $this->secureRandom = new SecureRandom();
        $this->inspector = new DecoratorInspector();
    }

    public function process(ContainerBuilder $container)
    {
        $decorators = [];

        foreach ($container->findTaggedServiceIds('decorator.decorate_this') as $serviceId => $decorateOthers) {

            foreach ($decorateOthers as $decorateOther) {

                if (!isset($decorateOther['decorator_id'])) {
                    throw UnexpectedValueException::missingContainerTagParameter(
                        'decorator_id',
                        'decorator.decorate_this',
                        $serviceId
                    );
                }

                if (!isset($decorators[$serviceId])) {
                    $decorators[$serviceId] = [];
                }

                $decorators[$serviceId][] = $decorateOther;
            }
        }

        foreach ($container->findTaggedServiceIds('decorator.decorate_other') as $decoratorId => $decoratorSubjects) {
            foreach ($decoratorSubjects as $decoratorSubject) {

                if (!isset($decoratorSubject['service_id'])) {
                    throw UnexpectedValueException::missingContainerTagParameter(
                        'service_id',
                        'decorator.decorate_other',
                        $decoratorId
                    );
                }

                $subjectServiceId = $decoratorSubject['service_id'];
                if (!isset($decorators[$subjectServiceId])) {
                    $decorators[$subjectServiceId] = [];
                }

                $decorators[$subjectServiceId][] = [
                    'decorator_id' => $decoratorId,
                    'priority'     => array_merge(['priority' => 0], $decoratorSubject)['priority'],
                ];
            }
        }

        foreach ($decorators as $serviceId => $serviceDecorators) {

            $serviceDecorators = array_reverse($serviceDecorators);
            usort($serviceDecorators, [$this, 'sortDecoratorsByPriority']);

            foreach ($serviceDecorators as $serviceDecorator) {
                $this->processDecorator($container, $serviceId, $serviceDecorator);
            }
        }
    }

    private function resolveClassName(ContainerBuilder $container, Definition $definition)
    {
        if ($definition instanceof DefinitionDecorator) {
            return $this->resolveClassName($container, $container->getDefinition($definition->getParent()));
        }

        return $definition->getClass();
    }

    private function processDecorator(ContainerBuilder $container, $serviceId, array $decorator)
    {
        $subjectDefinition = $container->getDefinition($serviceId);
        $decoratorDefinition = clone $container->getDefinition($decorator['decorator_id']);

        $sharedSupertypes = $this->inspector->inspectClassHierarchy(
            $this->resolveClassName($container, $subjectDefinition),
            $this->resolveClassName($container, $decoratorDefinition)
        );

        if (!$sharedSupertypes) {
            throw UnexpectedValueException::missingSharedSupertype(
                $subjectDefinition->getClass(),
                $serviceId,
                $decoratorDefinition->getClass(),
                $decorator['decorator_id']
            );
        }

        $wrappedServiceInsertionPosition = null;
        foreach ($decoratorDefinition->getArguments() as $position => $argument) {
            if ($argument instanceof Reference && (string) $argument === '__subject__') {
                $wrappedServiceInsertionPosition = $position;
            }
        }

        if ($wrappedServiceInsertionPosition === null) {
            throw InvalidArgumentException::missingContainerConstructorArgument(
                $decorator['decorator_id'],
                'service',
                '__subject__'
            );
        }

        $errorMessage = $this->inspector->inspectConstructorParameter(
            $decoratorDefinition->getClass(),
            $wrappedServiceInsertionPosition,
            $sharedSupertypes
        );
        if ($errorMessage) {
            throw new InvalidArgumentException($errorMessage);
        }

        $wrappedServiceId = $serviceId . base64_encode($this->secureRandom->nextBytes(256));

        $container->setDefinition($wrappedServiceId, $subjectDefinition);

        $decoratorDefinition->replaceArgument($wrappedServiceInsertionPosition, new Reference($wrappedServiceId));

        $decoratorDefinition->setPublic($subjectDefinition->isPublic());

        $subjectDefinition->setPublic(false);
        $container->setDefinition($serviceId, $decoratorDefinition);
    }

    private function sortDecoratorsByPriority(array $left, array $right)
    {
        static $default = ['priority' => 0];

        return strnatcmp(array_merge($default, $left)['priority'], array_merge($default, $right)['priority']);
    }
}
