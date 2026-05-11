<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class UserHandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $routeName = \Route::currentRouteName();
        $dataSession = '';
        if (session()->get('Message.flash')) {
            $dataSession = session()->get('Message.flash')[0];
        }
        $leftMenu = [
            [
                'label' => 'Trang chủ',
                'icon' => 'pi pi-fw pi-home',
                'to' => route('user.dashboard.index'),
                'active' => in_array($routeName, ['user.dashboard.index']),
            ],
            [
                'label' => 'Tình trạng bàn',
                'icon' => 'pi pi-fw pi-table',
                'to' => route('user.table.index'),
                'active' => in_array($routeName, ['user.table.index']),
            ],
        ];

        $breadcrumbs = [
            ['label' => 'Trang chủ', 'icon' => 'pi pi-home', 'url' => route('user.dashboard.index')],
        ];

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
            'user' => \Auth::guard('customer')->user(),
            'session' => $dataSession,
        ]);
    }
}
