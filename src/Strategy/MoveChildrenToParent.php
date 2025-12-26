<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Strategy;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;

class MoveChildrenToParent implements ChildrenHandler
{
    /**
     * @param Model|UseTree $model
     */
    public function handle(Model $model): void
    {
        $model->moveChildrenToParent();
    }
}
