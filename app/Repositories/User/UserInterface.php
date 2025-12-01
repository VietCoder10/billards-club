<?php

namespace App\Repositories\User;

use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserInterface
{
    public function get($request): LengthAwarePaginator;

    public function getById(string $id): ?User;

    public function store(StoreUserRequest $request): bool;

    public function update(StoreUserRequest $request, string $id): bool;

    public function destroy(string $id): bool;

    public function saveLoginHistory(): bool;

    public function checkEmail(Request $request): bool;
}
