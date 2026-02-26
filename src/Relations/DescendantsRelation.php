<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Relations;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Constraint;
use On1kel\NestedSet\QueryBuilder as NestedSetQueryBuilder;
use On1kel\NestedSet\UseNestedSet;

class DescendantsRelation extends BaseRelation
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (!Constraint::isConstraint()) {
            return;
        }

        $this->query->whereDescendantOf($this->parent)->applyNestedSetScope();
    }

    protected function addEagerConstraint(NestedSetQueryBuilder $query, Model $model): void
    {
        $query->whereDescendantOf($model, 'or');
    }

    /**
     * @param Model $model
     * @param Model|UseNestedSet $related
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
