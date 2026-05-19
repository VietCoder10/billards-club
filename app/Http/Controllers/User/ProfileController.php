<?php

namespace App\Http\Controllers\User;

use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends BaseController
{
    private CustomerInterface $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Show the profile edit form.
     */
    public function index()
    {
        $user = Auth::guard('customer')->user();
        return Inertia::render('User/Profile/Index', $this->mergeSession([
            'data' => [
                'title' => 'Thông tin cá nhân',
                'user' => $user,
            ]
        ]));
    }

    /**
     * Update the user profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::guard('customer')->user();
        if ($this->customer->update($request, $user->id)) {
            $this->setFlash(__('Cập nhật thông tin cá nhân thành công.'));
            return redirect()->route('user.profile.index');
        }
        $this->setFlash(__('Cập nhật thông tin cá nhân thất bại.'), 'error');
        return redirect()->route('user.profile.index');
    }

    /**
     * Check if email is available.
     */
    public function checkEmail(Request $request)
    {
        return response()->json([
            'valid' => $this->customer->checkEmail($request),
        ], StatusCode::OK);
    }
}
