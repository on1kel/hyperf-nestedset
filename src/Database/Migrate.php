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
        
        // Auto-detect parent_id type based on model's primary key type
        static::adjustParentTypeFromModel($builder, $instance);
        
        (new self($builder, $table))->buildColumns($excludeTreeCol);

        return $builder;
    }

    /**
     * Adjust parent_id type to match the model's primary key type
     */
    protected static function adjustParentTypeFromModel(Builder $builder, Model $model): void
    {
        $keyType = $model->getKeyType();
        $parentAttribute = $builder->parent();
        
        // Determine the appropriate field type based on key type
        $fieldType = match ($keyType) {
            'string' => \On1kel\NestedSet\Config\FieldType::UUID,
            'int', 'integer' => \On1kel\NestedSet\Config\FieldType::UnsignedBigInteger,
            default => $parentAttribute->type(),
        };
        
        // Update parent attribute type if needed
        if ($parentAttribute->type() !== $fieldType) {
            $parentAttribute->setType($fieldType);
        }
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
                ->nullable($attribute->nullable())
                ->comment($attribute->name()->comment());
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
        $treeColumnName = $this->builder->tree()?->columnName();
        
        // Add tree_id prefix only if it's multi-tree and tree_id is not already in columns
        if ($this->builder->isMulti() && !in_array($treeColumnName, $columns, true)) {
            array_unshift($columns, $treeColumnName);
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
