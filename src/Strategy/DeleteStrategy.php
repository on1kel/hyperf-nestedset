<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Strategy;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;

interface DeleteStrategy
{
    /**
     * @param Model|UseTree $model
     */
    public function handle(Model $model, bool $forceDelete): mixed;
}
