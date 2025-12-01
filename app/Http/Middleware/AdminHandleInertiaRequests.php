<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class AdminHandleInertiaRequests extends Middleware
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
            // session()->forget('Message.flash');
        }
        $leftMenu = [
            [
                'label' => 'ホーム',
                'icon' => 'pi pi-fw pi-home',
                'to' => route('admin.dashboard.index'),
                'active' => in_array($routeName, ['admin.dashboard.index']),
            ],
            [
                'label' => 'ユーザー一覧',
                'icon' => 'pi pi-fw pi-users',
                'to' => route('admin.user.index'),
                'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
            ]
        ];
        $breadcrumbs = [
            ['label' => 'ホーム', 'icon' => 'pi pi-home', 'url' => route('admin.dashboard.index')],
        ];
        if (in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update'])) {
            $breadcrumbs[] = ['label' => 'ユーザー一覧', 'icon' => 'pi pi-list', 'url' => session()->get('admin.user.list')[0] ?? route('admin.user.index')];
            if (in_array($routeName, ['admin.user.edit', 'admin.user.update'])) {
                $breadcrumbs[] = ['label' => 'ユーザー追加', 'icon' => 'pi pi-user-edit', 'url' => route('admin.user.edit', $request->user)];
            }
            if (in_array($routeName, ['admin.user.create', 'admin.user.store'])) {
                $breadcrumbs[] = ['label' => 'ユーザー追加', 'icon' => 'pi pi-user-plus', 'url' => route('admin.user.create')];
            }
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'dataSession' => $dataSession,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
            'menuDashboardActive' => in_array($routeName, ['admin.dashboard.index']),
            'menuUserActive' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
        ]);
    }
}
