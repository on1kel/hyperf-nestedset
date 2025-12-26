<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Exceptions;

use Hyperf\Database\Model\Model;

class UniqueRootException extends Exception
{
    public function __construct(protected Model $existRootModel, ?string $message = null)
    {
        if (!$message) {
            $message = 'Can not create more than one root. Exist: # ' . $this->existRootModel->getKey();
        }

        parent::__construct($message);
    }
}
