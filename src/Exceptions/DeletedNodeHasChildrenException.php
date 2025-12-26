<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Exceptions;

use Hyperf\Database\Model\Model;

class DeletedNodeHasChildrenException extends Exception
{
    public function __construct(protected Model $model)
    {
        parent::__construct('Deleted Node has children. #' . $this->model->getKey());
    }
}
