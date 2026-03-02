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
                'label' => 'Home',
                'icon' => 'pi pi-fw pi-home',
                'to' => route('admin.dashboard.index'),
                'active' => in_array($routeName, ['admin.dashboard.index']),
            ],
            [
                'label' => 'Play Session',
                'icon' => 'pi pi-play-circle',
                'to' => route('admin.order.index'),
            ],
            [
                'label' => 'Management',
                'icon' => 'pi pi-fw pi-pencil',
                'items' => [
                    [
                        'label' => 'Service Management',
                        'icon'  => 'pi pi-fw pi-building',
                        'items' => [
                            [
                                'label' => 'Supplier Management',
                                'icon'  => 'pi pi-fw pi-list',
                                'to' => route('admin.supplier.index'),
                                'active' => in_array($routeName, ['admin.supplier.index', 'admin.suppler.store', 'admin.suppler.update']),
                            ],
                            [
                                'label' => 'Product Management',
                                'icon'  => 'pi pi-fw pi-bookmark',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Staff Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Table Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Voucher Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Playtime Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Bill Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Order Management',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                ],
            ],
            [
                'label' => 'Revenue',
                'icon' => 'pi pi-fw pi-chart-bar',
            ],
        ];
        $breadcrumbs = [
            ['label' => 'Home', 'icon' => 'pi pi-home', 'url' => route('admin.dashboard.index')],
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
        if (in_array($routeName, ['admin.supplier.index', 'admin.supplier.create', 'admin.supplier.store', 'admin.supplier.edit', 'admin.supplier.update'])) {
            $breadcrumbs[] = ['label' => 'Supplier', 'icon' => 'pi pi-list', 'url' => route('admin.supplier.index')];
            if (in_array($routeName, ['admin.supplier.create', 'admin.supplier.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.create')];
            }
            if (in_array($routeName, ['admin.building.edit', 'admin.building.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.edit', $request->supplier)];
            }
        }
        if (in_array($routeName, ['admin.payment.index'])) {
            $breadcrumbs[] = ['label' => '調査・入金・返金・延滞', 'icon' => 'pi pi-list', 'url' => route('admin.payment.index')];
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'dataSession' => $dataSession,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
