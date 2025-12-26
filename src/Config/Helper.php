<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Config;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;

final readonly class Helper
{
    public static function isTreeNode(mixed $model): bool
    {
        return $model instanceof Model && (class_uses_recursive($model)[UseTree::class] ?? null);
    }

    public static function isModelSoftDeletable(Model|string $model): bool
    {
        return method_exists($model instanceof Model ? $model::class : $model, 'bootSoftDeletes');
    }
}
