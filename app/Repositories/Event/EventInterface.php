<?php

namespace App\Repositories\Event;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

interface EventInterface
{
    public function get(Request $request): Collection;
    public function store(Request $request): bool;
    public function destroy(string $id): bool;
}
