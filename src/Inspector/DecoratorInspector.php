<?php
namespace InterNations\Bundle\DecoratorBundle\Inspector;

use ReflectionClass;

/**
 * Enforces type rules on decorators
 */
class DecoratorInspector
{
    public function inspectClassHierarchy($subjectClassName, $decoratorClassName)
    {
        $sharedSupertypes = [];

        $subjectClass = new ReflectionClass($subjectClassName);
        $decoratorClass = new ReflectionClass($decoratorClassName);

        $subjectInterfaces = $subjectClass->getInterfaces();
        while (($subjectInterface = array_shift($subjectInterfaces))) {
            $decoratorInterfaces = $decoratorClass->getInterfaces();

            while (($decoratorInterface = array_shift($decoratorInterfaces))) {

                if ($subjectInterface == $decoratorInterface) {
                    $sharedSupertypes[] = $subjectInterface->getName();
                }

                $decoratorInterfaces = array_merge($decoratorInterfaces, $decoratorInterface->getInterfaces());
            }

            $subjectInterfaces = array_merge($subjectInterfaces, $subjectInterface->getInterfaces());
        }

        $currentSubjectClass = $subjectClass;
        do {
            $currentDecoratorClass = $decoratorClass;

            do {
                if ($currentSubjectClass == $currentDecoratorClass) {
                    $sharedSupertypes[] = $currentSubjectClass->getName();
                }
            } while (($currentDecoratorClass = $currentDecoratorClass->getParentClass()));

        } while (($currentSubjectClass = $currentSubjectClass->getParentClass()));

        return array_values(array_unique($sharedSupertypes));
    }

    public function inspectConstructorParameter($className, $parameterPosition, array $typeHints)
    {
        $class = new ReflectionClass($className);

        if (!$class->hasMethod('__construct')) {
            return sprintf('No constructor defined for class "%s"', $className);
        }

        $parameters = $class->getMethod('__construct')->getParameters();
        if (!isset($parameters[$parameterPosition])) {
            return sprintf(
                'No parameter at position %d in %s::__construct()',
                $parameterPosition,
                $className
            );
        }

        $parameter = $parameters[$parameterPosition];
        if (!$parameter->getClass()) {
            return sprintf(
                'Missing one of type hint for "%s" for parameter $%s at position %d in %s::__construct()',
                implode($typeHints, '", "'),
                $parameter->getName(),
                $parameterPosition,
                $className
            );
        }

        if (!in_array($parameter->getClass()->getName(), $typeHints, true)) {
            return sprintf(
                'Expected type hint for one of "%s" in %s::__construct() on position %d, got "%s"',
                implode($typeHints, '", "'),
                $className,
                $parameterPosition,
                $parameter->getClass()->getName()
            );
        }

        return null;
    }
}
