<?php

namespace App\Traits;

use App\Observers\SaveUserIdObserver;

trait SaveUserIdTrait
{
    public static function bootSaveUserIdTrait()
    {
        self::observe(SaveUserIdObserver::class);
    }
}
