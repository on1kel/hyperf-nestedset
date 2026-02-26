<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Relations;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Constraint;
use On1kel\NestedSet\QueryBuilder as NestedSetQueryBuilder;
use On1kel\NestedSet\UseNestedSet;

/**
 * Class AncestorsRelation
 */
class AncestorsRelation extends BaseRelation
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (!Constraint::isConstraint()) {
            return;
        }

        $this->query->whereAncestorOf($this->parent)->applyNestedSetScope();
    }

    protected function addEagerConstraint(NestedSetQueryBuilder $query, Model $model): void
    {
        $query->whereAncestorOf($model);
    }

    /**
     * @param Model $model
     * @param UseNestedSet $related
     *
     * @return mixed
     */
    protected function matches(Model $model, Model $related): bool
    {
        return $related->isChildOf($model);
    }

    protected function relationExistenceCondition(string $hash, string $table, string $lft, string $rgt): string
    {
        return "$hash.$lft between $table.$lft + 1 and $table.$rgt";
    }
}
