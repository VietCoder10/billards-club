<?php

namespace App\Repositories\User;

use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Admin\Login\AdminForgotPasswordRequest;
use App\Http\Requests\Admin\Login\AdminResetPasswordRequest;

interface UserInterface
{
    public function get($request): LengthAwarePaginator;

    public function getById(string $id): ?User;

    public function store(StoreUserRequest $request): bool;

    public function update(StoreUserRequest $request, string $id): bool;

    public function destroy(string $id): bool;

    public function saveLoginHistory(): bool;

    public function checkEmail(Request $request): bool;

    public function sendResetPasswordLink(AdminForgotPasswordRequest $request): bool;

    public function checkToken(string $token): ?User;

    public function resetPassword(AdminResetPasswordRequest $request, string $token): bool;
}
