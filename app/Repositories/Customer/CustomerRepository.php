<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\Auth\CustomerForgotPasswordRequest;
use App\Http\Requests\User\Auth\CustomerResetPasswordRequest;
use App\Http\Requests\User\Auth\CustomerRegisterRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;

class CustomerRepository implements CustomerInterface
{
    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function get($request) {}

    public function store($request): bool
    {
        $newCustomer = $this->customer->fill($request->only([
            'name',
            'email',
            'phone'
        ]));
        $newCustomer->password = Hash::make($request->password);

        return $newCustomer->save();
    }


    public function saveLoginHistory(): bool
    {
        $userInfo = $this->customer->where('id', Auth::guard('customer')->user()->id)->first();
        if ($userInfo) {
            $userInfo->last_login_at = Carbon::now();
            return $userInfo->save();
        }
        return false;
    }
    public function checkEmail(Request $request)
    {
        return ! $this->customer->where(function ($query) use ($request) {
            if (isset($request['id'])) {
                $query->where('id', '!=', $request['id']);
            }
            $query->where(['email' => $request['value']]);
        })->exists();
    }

    public function sendResetPasswordLink(CustomerForgotPasswordRequest $request): bool
    {
        $user = $this->customer->where('email', $request->email)->first();
        if (! $user) {
            return false;
        }
        $token = Str::random(64);
        $user->reset_password_token = $token;
        $user->reset_password_token_expire = Carbon::now()->addMinutes(30);

        if (! $user->save()) {
            return false;
        }
        $url = route('user.reset-password.show', $token);
        Mail::to($request->email)->send(new ForgotPassword([
            'url' => $url,
            'name' => $user->name,
        ]));
        return true;
    }

    public function checkToken(string $token): ?Customer
    {
        $user = $this->customer->where('reset_password_token', $token)
            ->where('reset_password_token_expire', '>', Carbon::now())
            ->first();

        return $user;
    }

    public function resetPassword(CustomerResetPasswordRequest $request, string $token): bool
    {
        $user = $this->checkToken($token);
        if (! $user) {
            return false;
        }
        $user->password = Hash::make($request->password);
        $user->reset_password_token = null;
        $user->reset_password_token_expire = null;

        return $user->save();
    }

    public function searchModal(Request $request)
    {
        $query = $this->customer->query();

        if ($request->search_name) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }

        if ($request->tel) {
            $query->where('phone', 'like', '%' . $request->tel . '%');
        }

        $sort = $request->sort ?? 'id';
        $direction = $request->direction ?? 'desc';
        $limit = $request->limit_page ?? 10;

        return $query->orderBy($sort, $direction)->paginate($limit);
    }

    public function storeModel(Request $request)
    {
        $customer = new $this->customer();
        $customer->name = $request->name;
        $customer->email = $request->email ?? (Str::random(10) . '@example.com');
        $customer->phone = $request->tel; // mapping tel to phone
        $customer->password = Hash::make($request->password ?? '12345678');
        
        if ($customer->save()) {
            return $customer;
        }
        return null;
    }
}
