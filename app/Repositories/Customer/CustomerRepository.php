<?php

namespace App\Repositories\Customer;

use App\Components\CommonComponent;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\Auth\CustomerForgotPasswordRequest;
use App\Http\Requests\User\Auth\CustomerResetPasswordRequest;
use App\Http\Requests\User\Auth\CustomerRegisterRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;
use App\Enums\StorageFolder;

class CustomerRepository implements CustomerInterface
{
    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->customer->query();
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request['free_word']}%");
                $query->orWhere('email', 'like', "%{$request['free_word']}%");
                $query->orWhere('phone', 'like', "%{$request['free_word']}%");
            });
        }
        $customers = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($customers)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $customers = $builder->paginate($newSizeLimit);
        }
        return $customers;
    }

    public function store($request): bool
    {
        $newCustomer = $this->customer->fill($request->only([
            'name',
            'email',
            'phone'
        ]));
        $newCustomer->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::AVATAR;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $newCustomer->avatar = $path;
        }

        return $newCustomer->save();
    }

    public function getById(string $id): ?Customer
    {
        return $this->customer->where('id', $id)->first();
    }

    public function update($request, string $id): bool
    {
        $customer = $this->getById($id);
        if (!$customer) {
            return false;
        }
        $customer->fill($request->only([
            'name',
            'email',
            'phone'
        ]));
        if ($request->password) {
            $customer->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                CommonComponent::deleteFile('', $customer->avatar);
            }
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::AVATAR;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $customer->avatar = $path;
        } elseif ($request->has('avatar') && $request->avatar === null) {
            if ($customer->avatar) {
                CommonComponent::deleteFile('', $customer->avatar);
                $customer->avatar = null;
            }
        }

        return $customer->save();
    }

    public function destroy(string $id): bool
    {
        $customer = $this->customer->where('id', $id)->first();
        if (!$customer) {
            return false;
        }

        return $customer->delete();
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

    public function getModalCustomer(Request $request)
    {


        $newSizeLimit = $request->input('limit_page', 10);
        $builder = $this->customer->query();
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request['free_word']}");
                $query->orWhere('email', 'like', "%{$request['free_word']}");
                $query->orWhere('phone', 'like', "%{$request['free_word']}");
            });
        }
        $customer = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($customer)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $customer = $builder->paginate($newSizeLimit);
        }
        return $customer;
    }

    public function storeModalCustomer(Request $request)
    {
        $customer = new Customer;
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->tel;
        $customer->password = Hash::make($request->password);

        if (!$customer->save()) {
            return false;
        }
        return $customer;
    }
}
