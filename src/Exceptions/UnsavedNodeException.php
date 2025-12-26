<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Exceptions;

use Hyperf\Database\Model\Model;

class UnsavedNodeException extends Exception
{
    public function __construct(protected Model $node, string $message = 'Node does not save')
    {
        parent::__construct($message);
    }
}
