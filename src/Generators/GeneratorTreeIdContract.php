<?php

declare(strict_types=1);

namespace On1kel\NestedSet\Generators;

use Hyperf\Database\Model\Model;

interface GeneratorTreeIdContract
{
    public function generateId(Model $model): string|int;
}
