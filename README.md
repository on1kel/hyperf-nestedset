# Hyperf NestedSet

![PHP Hyperf Package](https://img.shields.io/badge/php-8.2|8.3|8.4-blue.svg)
![Hyperf Version](https://img.shields.io/badge/Hyperf-3.1.*-red.svg)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

Пакет Hyperf для реализации многодеревьевых иерархических структур с использованием модели вложенных множеств (Nested Set Model).

## Обзор

Пакет поддерживает Multi-Tree структуры (несколько корневых узлов) и позволяет перемещать узлы между деревьями.
Работает с различными типами первичных ключей: `int`, `uuid` и `ulid`.

## Ключевые преимущества

- **Multi-Tree поддержка**: Управление несколькими независимыми деревьями в одной таблице
- **Кросс-дерево операции**: Легкое перемещение узлов между различными деревьями
- **Гибкие первичные ключи**: Работа с различными типами ключей, включая `int`, `uuid` и `ulid`
- **Оптимизация производительности**: Эффективное извлечение иерархических данных с минимальным количеством запросов к БД
- **Поддержка современного PHP**: Использование современных возможностей PHP 8.x и строгой типизации
- **Гибкая конфигурация**: Широкие возможности настройки имен атрибутов и поведения

### Что такое вложенные множества?

[Nested Set Model](http://en.wikipedia.org/wiki/Nested_set_model) — эффективный способ хранения иерархических данных в
реляционных базах данных:

> Модель вложенных множеств нумерует узлы в соответствии с обходом дерева, который посещает каждый узел дважды,
> присваивая номера в порядке посещения при каждом визите. Это оставляет два числа для каждого узла, которые хранятся
> как атрибуты. Запросы становятся недорогими: принадлежность к иерархии можно проверить сравнением этих чисел.
> Обновление требует перенумерации и поэтому является дорогим.

### Идеальные случаи использования

NSM показывает хорошую производительность когда:

- Деревья обновляются редко
- Требуется быстрое извлечение связанных узлов
- Построение многоуровневых меню или структур категорий

## Требования

- PHP: 8.2|8.3|8.4
- Hyperf: ^3.1.*

Настоятельно рекомендуется использовать базу данных, поддерживающую транзакции (например, PostgreSQL), для защиты
структуры дерева от повреждения.

## Установка

```shell
composer require on1kel/hyperf-nestedset
```

## Базовое использование

### Настройка модели

Чтобы сделать модель узлом дерева, добавьте трейт `UseTree`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;

class Category extends Model
{
    use UseTree;

    protected ?string $table = 'categories';

    protected array $fillable = ['name', 'parent_id'];
}
```

### Миграция

Используйте хелпер миграции для создания необходимых колонок:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;
use On1kel\NestedSet\Database\Migrate;
use App\Model\Category;

class CreateCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Добавить колонки nested set из модели
            Migrate::columnsFromModel($table, Category::class);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
}
```

### Создание узлов

#### Создание корневого узла

```php
// Для модели с одним деревом
$root = new Category(['name' => 'Корень']);
$root->saveAsRoot();

// Или используя makeRoot()
$root = new Category(['name' => 'Корень']);
$root->makeRoot()->save();
```

#### Создание дочерних узлов

```php
// Добавить в конец (как последний дочерний)
$child = new Category(['name' => 'Дочерний']);
$child->appendTo($parent)->save();

// Добавить в начало (как первый дочерний)
$child = new Category(['name' => 'Дочерний']);
$child->prependTo($parent)->save();
```

#### Создание соседних узлов

```php
// Вставить перед узлом
$sibling = new Category(['name' => 'Сосед']);
$sibling->insertBefore($existingNode)->save();

// Вставить после узла
$sibling = new Category(['name' => 'Сосед']);
$sibling->insertAfter($existingNode)->save();
```

### Перемещение узлов

```php
// Переместить узел к другому родителю
$node->appendTo($newParent)->save();

// Переместить узел перед соседом
$node->insertBefore($sibling)->save();

// Переместить узел после соседа
$node->insertAfter($sibling)->save();

// Переместить вверх среди соседей
$node->up();

// Переместить вниз среди соседей
$node->down();
```

### Получение узлов

#### Получить корень

```php
$root = Category::root()->first();
// или
$root = $node->getRoot();
```

#### Получить родителя

```php
$parent = $node->parent;
```

#### Получить детей

```php
$children = $node->children;
```

#### Получить предков

```php
$ancestors = $node->ancestors;
// или через query builder
$ancestors = $node->parents();
```

#### Получить потомков

```php
$descendants = $node->descendants;
// или через query builder
$descendants = $node->newQuery()->descendantsQuery()->get();
```

#### Получить соседей

```php
$siblings = $node->siblings()->get();
$siblingsAndSelf = $node->siblingsAndSelf()->get();
```

#### Получить листья (узлы без детей)

```php
$leaves = $node->leaves()->get();
```

### Построение дерева

```php
$tree = Category::defaultOrder()->get()->toTree();
```

### Проверка состояния узла

```php
$node->isRoot();    // Это корневой узел?
$node->isLeaf();    // Это лист (без детей)?
$node->isChildOf($otherNode);  // Это дочерний узел другого?
```

### Удаление узлов

```php
// Удалить узел (дети будут перемещены к родителю)
$node->delete();

// Удалить узел со всеми потомками
$node->deleteWithChildren();
```

## Поддержка Multi-Tree

Для включения поддержки нескольких деревьев, переопределите метод `buildTree`:

```php
<?php

namespace App\Model;

use Hyperf\Database\Model\Model;
use On1kel\NestedSet\UseTree;
use On1kel\NestedSet\Config\Builder;

class Category extends Model
{
    use UseTree;

    protected static function buildTree(): Builder
    {
        return Builder::defaultMulti();
    }
}
```

Затем можно работать с несколькими деревьями:

```php
// Создать корни для разных деревьев
$tree1Root = new Category(['name' => 'Корень дерева 1']);
$tree1Root->setTree(1)->saveAsRoot();

$tree2Root = new Category(['name' => 'Корень дерева 2']);
$tree2Root->setTree(2)->saveAsRoot();

// Запрос по дереву
$tree1Nodes = Category::byTree(1)->get();
```

## Query Scopes

```php
// Получить только корневые узлы
Category::root()->get();

// Получить узлы по дереву (только multi-tree)
Category::byTree($treeId)->get();

// Получить узлы по уровню
Category::byLevel(2)->get();

// Получить узлы до определенного уровня
Category::toLevel(3)->get();

// Сортировка по умолчанию (по левому атрибуту)
Category::defaultOrder()->get();
```

## Лицензия

MIT License. См. [LICENSE.md](LICENSE.md) для деталей.

## Благодарности

Этот пакет является портом [efureev/laravel-trees](https://github.com/efureev/laravel-trees) для Hyperf.
