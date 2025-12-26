<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Relations;

use Hyperf\Database\Model\Collection as EloquentCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Builder;
use InvalidArgumentException;
use On1kel\NestedSet\Collection;
use On1kel\NestedSet\Config\Helper;
use On1kel\NestedSet\QueryBuilder as NestedSetQueryBuilder;
use On1kel\NestedSet\UseNestedSet;

/**
 * Class BaseRelation
 * @package On1kel\NestedSet
 *
 * @property NestedSetQueryBuilder $query
 * @property UseNestedSet|Model
 */
abstract class BaseRelation extends Relation
{
    public function __construct(NestedSetQueryBuilder $builder, Model $parent)
    {
        if (!Helper::isTreeNode($parent)) {
            throw new InvalidArgumentException('Model must be a node.');
        }

        parent::__construct($builder, $parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation): array
    {
        return $models;
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults(): Collection
    {
        return $this->query->get();
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models): void
    {
        if (isset($models[0])) {
            $models[0]->applyNestedSetScope($this->query);
        }
        
        $this->query->whereNested(
            function (Builder $inner) use ($models) {
                $outer = $this->parent->newQuery()->setQuery($inner);
                foreach ($models as $model) {
                    $this->addEagerConstraint($outer, $model);
                }
            }
        );
    }

    abstract protected function addEagerConstraint(NestedSetQueryBuilder $query, Model $model): void;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param Model[] $models
     * @param EloquentCollection $results
     * @param string $relation
     */
    public function match(array $models, EloquentCollection $results, $relation): array
    {
        foreach ($models as $model) {
            $related = $this->matchForModel($model, $results);
            $model->setRelation($relation, $related);
        }

        return $models;
    }

    /**
     * @return EloquentCollection
     */
    protected function matchForModel(Model $model, EloquentCollection $results): EloquentCollection
    {
        $result = $this->related->newCollection();
        foreach ($results as $related) {
            if ($this->matches($model, $related)) {
                $result->push($related);
            }
        }

        return $result;
    }

    abstract protected function matches(Model $model, Model $related): bool;

    abstract protected function relationExistenceCondition(
        string $hash,
        string $table,
        string $lft,
        string $rgt
    ): string;
}
