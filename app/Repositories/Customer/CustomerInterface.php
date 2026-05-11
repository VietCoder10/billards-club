<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\User\Auth\CustomerForgotPasswordRequest;
use App\Http\Requests\User\Auth\CustomerResetPasswordRequest;
use App\Http\Requests\User\Auth\CustomerRegisterRequest;

interface CustomerInterface
{
    public function get($request);

    public function store($request): bool;

    public function saveLoginHistory(): bool;

    public function checkEmail(Request $request);

    public function sendResetPasswordLink(CustomerForgotPasswordRequest $request): bool;

    public function checkToken(string $token): ?Customer;

    public function resetPassword(CustomerResetPasswordRequest $request, string $token): bool;
}
