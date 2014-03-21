<?php
namespace InterNations\Bundle\DecoratorBundle\Exception;

use UnexpectedValueException as BaseUnexpectedValueException;

class UnexpectedValueException extends BaseUnexpectedValueException implements ExceptionInterface
{
    public static function missingContainerTagParameter($parameter, $tag, $serviceId)
    {
        return new static(
            sprintf(
                'Missing parameter "%s" in tag named "%s" for service "%s"',
                $parameter,
                $tag,
                $serviceId
            )
        );
    }

    public static function missingSharedSupertype(
        $subjectClassName,
        $subjectServiceId,
        $decoratorClassName,
        $decoratorServiceId
    )
    {
        return new static(
            sprintf(
                '"%s" ("%s") is configured as a decorator for "%s" ("%s") but do not share a common supertype. '
                . 'Implement a shared interface (e.g. by introducing "%sInterface") or extend a shared baseclass',
                $decoratorClassName,
                $decoratorServiceId,
                $subjectClassName,
                $subjectServiceId,
                $subjectClassName
            )
        );
    }
}
