<?php

declare(strict_types=1);

namespace Studio15\FilamentTree\Concerns;

use Kalnoy\Nestedset\NodeTrait;

/**
 * Use it to integrate model to tree
 *
 * @mixin NodeTrait
 */
trait InteractsWithTree
{
    /**
     * @return non-empty-string
     */
    abstract public static function getTreeLabelAttribute(): string;

    public function getTreeCaption(): ?string
    {
        return null;
    }

    public function getModelLabel(): ?string
    {
        return null;
    }
}
