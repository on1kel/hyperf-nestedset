<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Exceptions;

use Hyperf\Database\Model\Model;

class DeleteRootException extends Exception
{
    public function __construct(protected Model $model)
    {
        parent::__construct('Root node does not support delete action. #' . $this->model->getKey());
    }
}
