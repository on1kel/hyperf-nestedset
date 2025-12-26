<?php

declare(strict_types=1);

namespace On1kel\NestedSet;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\Config\Helper;

/**
 * @method QueryBuilder newQuery()
 * @mixin QueryBuilder
 */
trait WithQueryBuilder
{
    public function newEloquentBuilder($query): QueryBuilder
    {
        return new QueryBuilder($query);
    }

    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }

    /**
     * @return static|null
     */
    public function getRoot(): ?Model
    {
        return $this->newQuery()
            ->root()
            ->first();
    }

    /**
     * @param Model|static $node
     */
    public function isChildOf(Model $node): bool
    {
        return $this->treeValue() === $node->treeValue() &&
            $this->leftValue() > $node->leftValue() &&
            $this->rightValue() < $node->rightValue();
    }

    /**
     * Is a leaf-node
     */
    public function isLeaf(): bool
    {
        $delta = ($this->rightValue() - $this->leftValue());
        if ($delta === 1) {
            return true;
        }

        if (!$this->isSoftDelete()) {
            return false;
        }

        if ($this->relationLoaded('children')) {
            $children = $this->getRelation('children');
            return $children->count() === 0;
        }

        return $this->children()->count() === 0;
    }

    public function newNestedSetQuery(?string $table = null): QueryBuilder
    {
        $builder = $this->isSoftDelete()
            ? $this->withTrashed()
            : $this->newQuery();

        return $this->applyNestedSetScope($builder, $table);
    }

    public function newScopedQuery($table = null): QueryBuilder
    {
        return $this->applyNestedSetScope($this->newQuery(), $table);
    }

    public function applyNestedSetScope(QueryBuilder $builder, ?string $table = null): QueryBuilder
    {
        if (!$scoped = $this->getScopeAttributes()) {
            return $builder;
        }

        if (!$table) {
            $table = $this->getTable();
        }

        foreach ($scoped as $attribute) {
            $builder->where("$table.$attribute", '=', $this->getAttributeValue($attribute));
        }

        return $builder;
    }

    protected function getScopeAttributes(): array
    {
        return [];
    }

    /**
     * @return (string|int)[]
     */
    public function getNodeBounds(Model|string|int $node): array
    {
        if (Helper::isTreeNode($node)) {
            /** @var UseTree $node */
            return $node->getBounds();
        }

        return $this->newNestedSetQuery()->getPlainNodeData($node, true);
    }
}
