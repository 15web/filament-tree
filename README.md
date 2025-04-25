# Eloquent tree with Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/15web/filament-tree.svg?style=flat-square)](https://packagist.org/packages/15web/filament-tree)
[![Build and check code status](https://github.com/15web/filament-tree/actions/workflows/check.yml/badge.svg)](https://github.com/15web/filament-tree/actions)
![PHP Version](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-11.0-red?style=flat-square&logo=laravel)
![Filament Version](https://img.shields.io/badge/Filament-3.2-orange?style=flat-square)

#### Build the tree from your Eloquent model with Filament

This plugin offers a tree builder for the Filament admin panel,
allows you to build menu, category tree and etc. and management.

Advantages of the plugin:

- Isolation of elements (the tree is not rebuilt when editing, when changing a parent - only the changed nodes are
  re-rendered).
- “Rememberability” of the collapse state. By default, all nodes are collapsed, as a result, the “children” are not
  rendered so the page loads quickly.
- Display of any attributes (available here https://filamentphp.com/docs/3.x/infolists/entries/getting-started) of model
  in the tree, which can be useful for content visualization
- The component is all-sufficient as a resource, there is no need for separate pages for creating, editing, listing
  models.
- For integration, it is enough to add just a trait (or two, if there was no integration with Nested Set) to the model
  and specify the name of the attribute that will be used as the node header in the tree.

![Dark Theme](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/dark.jpg?raw=true)  
![Light Theme](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/light.jpg?raw=true)

Table of Contents:

- [Installation](#installation)
- [Prepare your model](#prepare-your-model)
- [Create the tree page](#create-the-tree-page)
- [Configuration](#configuration)
- [Customization](#customization)
- [Advanced features](#advanced-features)

## Installation

![Installation](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/install.jpg?raw=true)

You can install the package via composer:

```bash
composer require 15web/filament-tree
```

Add the plugin service provider to  `bootstrap/providers.php`:

```php
<?php

return [
    // ...
    Studio15\FilamentTree\FilamentTreeServiceProvider::class,
];
```

## Prepare your model

### Trait

#### A.

You have the existing model with [Nested Set](https://github.com/lazychaser/laravel-nestedset) integration.  
Just add `InteractsWithTree` trait.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Studio15\FilamentTree\Concerns\InteractsWithTree;

class AwesomeModel extends Model
{
    use NodeTrait;
    use InteractsWithTree;
```

#### B.

If your model are "clean", so please follow next steps.

1. Add `NodeTrait` and `InteractsWithTree` traits to the model.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Studio15\FilamentTree\Concerns\InteractsWithTree;

class AwesomeModel extends Model
{
    use NodeTrait;
    use InteractsWithTree;
```

2. Create new migration

```shell
php artisan make:migration add_tree_to_awesome_model --table=awesome_model_table
```

And add columns:

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('awesome_model_table', function (Blueprint $table) {
            $table->nestedSet();
        });
    }
```

And run the migration:

```shell
php artisan migrate
```

### Tree label attribute

Then please define attribute name of the nodes in your tree, eg. `title`, add method to the model:

```php
public static function getTreeLabelAttribute(): string
{
    return 'title';
}
```

Your model is ready.

## Create the tree page

To add the tree page to your admin panel,  
call artisan command and input name of page and the model class:

```shell
php artisan make:filament-tree-page
```

You can setup fields you need while you create or edit any of tree record.
Fill the `getCreateForm` and `getEditForm` in your tree page, eg.

```php
public static function getCreateForm(): array
{
    return [
        TextInput::make('title')->required(),
        TextInput::make('slug')->required()->unique(ignoreRecord: true),
    ];
}

public static function getEditForm(): array
{
    return [
        TextInput::make('title')->required(),
        TextInput::make('slug')->required()->unique(ignoreRecord: true),
        Toggle::make('is_published'),
        TextInput::make('description')->nullable(),
    ];
}
```

Read more about form fields at  
https://filamentphp.com/docs/3.x/forms/getting-started

![Create Form](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/create.jpg?raw=true)  
![Edit form](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/edit.jpg?raw=true)  
![Delete Confirmation](https://raw.githubusercontent.com/15web/filament-tree/refs/heads/main/assets/delete.jpg?raw=true)

That's all!  
Now you can manage your tree based on the model!

## Configuration

* `allow-delete-parent`  
  You can restrict to delete nodes having children items.

* `allow-delete-root`  
  You can restrict to delete root nodes, even if 'allow-delete-parent' is true.

* `show-parent-select-while-edit`  
  If you want to see edit form as compact one, you able to remove parent's select from it.
  You still can drag'n'drop the nodes.

You can publish config with:

```bash
php artisan vendor:publish --tag="filament-tree-config"
```

## Customization

### Caption

To display any attribute as second line of node label, please add the method to you model and define the caption value:

```php
public function getTreeCaption(): ?string
{
    return $this->description;
}
```

### Infolist

To display any meta information next to node label, you able to fill the method of your page with Infolist entries, eg.

```php
public static function getInfolistColumns(): array
{
    return [
        TextEntry::make('description')->label(fn(Category $record, Get $get) => $record->description !== null ? 'Desc' : ''),
        IconEntry::make('is_published')->boolean()->label(''),
    ];
} 
```

Read more at  
https://filamentphp.com/docs/3.x/infolists/entries/getting-started#available-entries

Please note,  
created tree page extends Filament Page, so all customizations are available.  
Get know about at https://filamentphp.com/docs/3.x/panels/pages

### Localization

You can publish translations with:

```bash
php artisan vendor:publish --tag="filament-tree-translations"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-tree-views"
```

## Advanced features

### Scope

You can have as many models as you want in your project, and you can add a tree page for each one.  
But what if your project has multiple menus (for example, in the header and footer) that have the same attributes?
What if the menu items are stored in one table?
You can create a separate page for each of your menus using scopes.

To do this, specify the attribute by which the item belongs to the menu (eg. `menu_id`), add method to your model:

```php
public function getScopeAttributes(): array
{
    return ['menu_id'];
}
```

In you tree page, specify how exactly you need to get the menu items for a specific admin page:

```php
final class AwesomeTree extends TreePage
{
    public static function getModel(): string|QueryBuilder
    {
        return AwesomeModel::scoped(['menu_id' => 2]);
    }
```

That's all!

Please read more at  
https://github.com/lazychaser/laravel-nestedset?tab=readme-ov-file#scoping

### Fix tree

If you have changed the structure of your tree, you need to rebuild the relationships of all nodes.  
To do this, use the button "Fix tree" at the footer of your tree page.

## Support and feedback

If you find a bug, please submit an issue directly to GitHub.
[Filament Tree Issues](https://github.com/15web/filament-tree/issues)

As always, if you need further support, please contact us.
https://www.15web.ru/contacts

## Copyright and license

Copyright © [Studio 15](http://15web.ru), 2012 - Present.   
Code released under [the MIT license](https://opensource.org/licenses/MIT).