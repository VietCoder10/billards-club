<?php

namespace App\Repositories\Building;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Admin\Building\BuildingRequest;
use App\Models\Building;

interface BuildingInterface
{
    public function get(Request $request): LengthAwarePaginator;
    public function getById(string $id): ?Building;
    public function store(BuildingRequest $request): bool;
    public function update(BuildingRequest $request, string $id): bool;
    public function destroy(string $id): bool;

    public function checkCode(Request $request): bool;
}
