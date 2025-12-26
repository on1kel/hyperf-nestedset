<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Database;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Blueprint;
use On1kel\NestedSet\Config\Builder;
use On1kel\NestedSet\Exceptions\Exception;

final readonly class Migrate
{
    public function __construct(
        protected Builder $builder,
        protected Blueprint $table
    ) {
    }

    /**
     * @throws Exception
     */
    public static function columnsFromModel(Blueprint $table, Model|string $model, bool $excludeTreeCol = false): Builder
    {
        $instance = is_string($model) ? new $model() : $model;

        if (!method_exists($instance, 'getTreeBuilder')) {
            throw new Exception('Model does not implement tree structure');
        }

        $builder = $instance->getTreeBuilder();
        (new self($builder, $table))->buildColumns($excludeTreeCol);

        return $builder;
    }

    /**
     * Add default nested set columns to the table. Also create an index.
     */
    public function buildColumns(bool $excludeTreeCol = false): void
    {
        $this->addTreeColumns($excludeTreeCol);
        $this->buildIndexes();
    }

    /**
     * Adds tree structure columns to the table.
     */
    private function addTreeColumns(bool $excludeTreeCol): void
    {
        foreach ($this->builder->columnsList() as $attribute) {
            if ($excludeTreeCol && $attribute->name()->isTreeType()) {
                continue;
            }

            $this->table->{$attribute->type()->value}($attribute->columnName())
                ->default($attribute->default())
                ->nullable($attribute->nullable());
        }
    }


    private function buildIndexes(): void
    {
        foreach ($this->builder->columnIndexes() as $indexName => $columns) {
            $this->createIndex($indexName, (array)$columns);
        }
    }

    /**
     * Creates a single index, adding tree column for multi-tree structures.
     *
     * @param string $indexName Base name for the index
     * @param array $columns Columns to include in the index
     */
    private function createIndex(string $indexName, array $columns): void
    {
        if ($this->builder->isMulti()) {
            array_unshift($columns, $this->builder->tree()->columnName());
        }

        $indexFullName = $this->table->getTable() . "_{$indexName}_idx";
        $this->table->index($columns, $indexFullName);
    }


    /**
     * Drops all nested set columns and their indexes.
     */
    public function dropColumns(): void
    {
        $this->dropTreeIndexes();
        $this->dropTreeColumns();
    }

    /**
     * Drops all tree structure indexes.
     */
    private function dropTreeIndexes(): void
    {
        foreach ($this->builder->columnIndexes() as $indexName => $columns) {
            $this->table->dropIndex($indexName);
        }
    }

    /**
     * Drops all tree structure columns.
     */
    private function dropTreeColumns(): void
    {
        foreach ($this->builder->columnsNames() as $column) {
            $this->table->dropColumn($column);
        }
    }
}
