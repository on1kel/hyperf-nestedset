<?php

declare(strict_types=1);

if (!function_exists('instance')) {
    /**
     * Create a new instance of the given class.
     *
     * @template T
     * @param class-string<T> $class
     * @param mixed ...$params
     * @return T
     */
    function instance(string $class, mixed ...$params): object
    {
        return new $class(...$params);
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|string $class
     * @return array
     */
    function class_uses_recursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class) ?: []) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param string $trait
     * @return array
     */
    function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}
