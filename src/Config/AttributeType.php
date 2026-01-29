<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Config;

enum AttributeType: string
{
    case Left   = 'left';
    case Right  = 'right';
    case Level  = 'level';
    case Parent = 'parent_id';
    case Tree   = 'tree_id';

    public function isTreeType(): bool
    {
        return $this === self::Tree;
    }

    public function comment(): string
    {
        return match ($this) {
            self::Left => 'Левая граница узла',
            self::Right => 'Правая граница узла',
            self::Level => 'Уровень вложенности в дереве',
            self::Parent => 'Идентификатор родительского узла',
            self::Tree => 'Идентификатор дерева для мульти-деревьев',
        };
    }
}
