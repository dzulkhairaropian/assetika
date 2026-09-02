<?php

namespace App\Traits;

use App\Models\Scopes\DataScope;

trait HasDataScope
{
    /**
     * Boot the data scope trait for a model.
     */
    protected static function bootHasDataScope(): void
    {
        static::addGlobalScope(new DataScope);
    }
}
