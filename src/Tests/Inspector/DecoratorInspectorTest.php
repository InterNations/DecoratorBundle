<?php
namespace InterNations\Bundle\DecoratorBundle\Tests\Inspector;

use InterNations\Bundle\DecoratorBundle\Inspector\DecoratorInspector;
use InterNations\Component\Testing\AbstractTestCase;

class DecoratorInspectorTest extends AbstractTestCase
{
    /** @var DecoratorInspector */
    private $inspector;

    public function setUp(): void
    {
        $this->inspector = new DecoratorInspector();
    }

    public static function getHierarchies(): array
    {
        return [
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass2ndLevel',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass2ndLevelDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass2ndLevelDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass2ndLevel',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass3ndLevel',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass3ndLevelDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClassBase'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass2ndLevel',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingSharedBaseClass3ndLevelDecorator'
            ],
            [
                [
                    __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingInterfaceExtendedByInterface',
                    __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingSharedInterface',
                ],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceClass',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingSharedInterface'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingSharedInterfaceClass2ndLevel',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingSharedInterface'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceClass',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorImplementingSharedInterface2ndLevelDecorator'
            ],
            [
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass'],
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass',
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClassDecorator'
            ],
            [
                ['Iterator', 'Traversable'],
                'ArrayIterator',
                'IteratorIterator'
            ],
        ];
    }

    /** @dataProvider getHierarchies */
    public function testValidCaseValidCaseDecoratorExtendingSharedBaseClass(
        array $sharedSupertypes,
        string $subjectClassName,
        string $decoratorClassName
    ): void
    {
        $this->assertSame(
            $sharedSupertypes,
            $this->inspector->inspectClassHierarchy($subjectClassName, $decoratorClassName)
        );
    }

    public function testInvalidCaseNoSharedSuperTypes(): void
    {
        $this->assertEmpty($this->inspector->inspectClassHierarchy('SplFileInfo', 'Iterator'));
    }

    public function testValidCaseInspectingConstructorParameter(): void
    {
        $this->assertNull(
            $this->inspector->inspectConstructorParameter(
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClassDecorator',
                1,
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass']
            )
        );
    }

    public function testInvalidCaseNoTypeHint(): void
    {
        $this->assertSame(
            'Missing one of type hint for "InterNations\Bundle\DecoratorBundle\Tests\Inspector\ValidCaseDecoratorExtendingDecoratedClass" for parameter $str at position 0 in InterNations\Bundle\DecoratorBundle\Tests\Inspector\ValidCaseDecoratorExtendingDecoratedClassDecorator::__construct()',
            $this->inspector->inspectConstructorParameter(
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClassDecorator',
                0,
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass']
            )
        );
    }

    public function testInvalidCaseNoConstructor(): void
    {
        $this->assertSame(
            'No constructor defined for class "stdClass"',
            $this->inspector->inspectConstructorParameter(
                'stdClass',
                0,
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass']
            )
        );
    }

    public function testInvalidCaseParameterOutOfBounds(): void
    {
        $this->assertSame(
            'No parameter at position 1000 in InterNations\Bundle\DecoratorBundle\Tests\Inspector\ValidCaseDecoratorExtendingDecoratedClassDecorator::__construct()',
            $this->inspector->inspectConstructorParameter(
                __NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClassDecorator',
                1000,
                [__NAMESPACE__ . '\\' . 'ValidCaseDecoratorExtendingDecoratedClass']
            )
        );
    }
}

class ValidCaseDecoratorExtendingDecoratedClass
{
}

class ValidCaseDecoratorExtendingDecoratedClassDecorator extends ValidCaseDecoratorExtendingDecoratedClass
{
    public function __construct($str, ValidCaseDecoratorExtendingDecoratedClass $subject)
    {
    }
}

abstract class ValidCaseDecoratorExtendingSharedBaseClassBase
{
}

class ValidCaseDecoratorExtendingSharedBaseClass extends ValidCaseDecoratorExtendingSharedBaseClassBase
{
}

class ValidCaseDecoratorExtendingSharedBaseClassDecorator extends ValidCaseDecoratorExtendingSharedBaseClassBase
{
}

class ValidCaseDecoratorExtendingSharedBaseClass2ndLevel extends ValidCaseDecoratorExtendingSharedBaseClass
{
}

class ValidCaseDecoratorExtendingSharedBaseClass2ndLevelDecorator extends ValidCaseDecoratorExtendingSharedBaseClassDecorator
{
}

class ValidCaseDecoratorExtendingSharedBaseClass3ndLevel extends ValidCaseDecoratorExtendingSharedBaseClass2ndLevel
{
}

class ValidCaseDecoratorExtendingSharedBaseClass3ndLevelDecorator extends ValidCaseDecoratorExtendingSharedBaseClass2ndLevelDecorator
{
}

interface ValidCaseDecoratorImplementingSharedInterface
{
}

class ValidCaseDecoratorImplementingSharedInterfaceClass implements ValidCaseDecoratorImplementingSharedInterface
{
}

class ValidCaseDecoratorImplementingSharedInterfaceDecorator implements ValidCaseDecoratorImplementingSharedInterface
{
}

class ValidCaseDecoratorImplementingSharedInterfaceClass2ndLevel extends ValidCaseDecoratorImplementingSharedInterfaceClass
{
}

class ValidCaseDecoratorImplementingSharedInterface2ndLevelDecorator extends ValidCaseDecoratorImplementingSharedInterfaceDecorator
{
}

interface ValidCaseDecoratorImplementingInterfaceExtendedByInterface extends ValidCaseDecoratorImplementingSharedInterface
{
}

class ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceClass implements ValidCaseDecoratorImplementingInterfaceExtendedByInterface
{
}

class ValidCaseDecoratorImplementingInterfaceExtendedByInterfaceDecorator implements ValidCaseDecoratorImplementingInterfaceExtendedByInterface
{
}
