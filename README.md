# DecoratorBundle for Symfony2

Provides consistent decorator handling for the Symfony2 Dependency Injection Container.


## "Decorate this"-mode

In "decorate this"-mode you declare your decorators in the dependency at hand. A configuration example:

```xml
<service id="iterator" class="ArrayIterator">
    <argument type="collection">
        <argument type="string">element1</argument>
        <argument type="string">element2</argument>
    </argument>
    <tag name="decorator.decorate_this" decorator_id="infinite_iterator"/>
</service>

<service id="infinite_iterator" class="InifiteIterator" public="false">
    <argument type="service" id="__subject__"/>
</service>
```

The configuration above will create this instance:

```php
$iterator = new InfiniteIterator(
    new ArrayIterator(['element1', 'element2'])
);
```

## "Decorate other" mode

In "decorate other"-mode you declare the subjects you are decorating in the decorator definition itself. A configuration
example:

```xml
<service id="iterator" class="ArrayIterator">
    <argument type="collection">
        <argument type="string">element1</argument>
        <argument type="string">element2</argument>
    </argument>
</service>

<service id="infinite_iterator" class="InifiteIterator" public="false">
    <argument type="service" id="__subject__"/>
    <tag name="decorator.decorate_other" service_id="iterator"/>
</service>
```

The configuration above will again create this instance:

```php
$iterator = new InfiniteIterator(
    new ArrayIterator(['element1', 'element2'])
);
```


## Specifying priorities

To control the order of decoration, setting a priority flag for the decorator is supported. Priority can be between
`PHP_INT_MAX` and `-PHP_INT_MAX`, the default priority is `0`.

```xml
...
<service id="infinite_iterator" class="InifiteIterator" public="false">
    <argument type="service" id="__subject__"/>
    <tag name="decorator.decorate_other" service_id="iterator" priority="255"/>
</service>
```
