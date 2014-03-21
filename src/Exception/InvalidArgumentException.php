<?php
namespace InterNations\Bundle\DecoratorBundle\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
    public static function missingContainerConstructorArgument($decoratorServiceId, $type, $name)
    {
        return new static(
            sprintf(
                'Decorator service "%s" does not declare a constructor argument of type "%s" with name "%s"',
                $decoratorServiceId,
                $type,
                $name
            )
        );
    }
}
