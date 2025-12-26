<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Strategy;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;

class DeleteWithChildren implements DeleteStrategy
{
    /**
     * @param Model|UseTree $model
     */
    public function handle(Model $model, bool $forceDelete): mixed
    {
        $query = $model->newQuery()->descendantsQuery(null, true);
        
        if ($forceDelete) {
            return $query->forceDelete();
        }
        
        return $query->delete();
    }
}
