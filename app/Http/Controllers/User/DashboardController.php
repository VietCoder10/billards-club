<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Inertia\Inertia;

class DashboardController extends BaseController
{
    public function index()
    {
        return Inertia::render('User/Dashboard/Index', [
            'data' => [
                'title' => 'Trang chủ',
            ],
        ]);
    }
}
