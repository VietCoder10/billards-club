<?php

return [
    App\Providers\AppServiceProvider::class,
    Barryvdh\Debugbar\ServiceProvider::class,
    Kyslik\ColumnSortable\ColumnSortableServiceProvider::class,
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    L5Swagger\L5SwaggerServiceProvider::class,
    App\Providers\ApiResponseServiceProvider::class,
];
