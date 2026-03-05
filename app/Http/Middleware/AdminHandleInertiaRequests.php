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
                'label' => 'Trang chủ',
                'icon' => 'pi pi-fw pi-home',
                'to' => route('admin.dashboard.index'),
                'active' => in_array($routeName, ['admin.dashboard.index']),
            ],
            [
                'label' => 'Phiên chơi',
                'icon' => 'pi pi-play-circle',
                'to' => route('admin.order.index'),
            ],
            [
                'label' => 'Quản lý',
                'icon' => 'pi pi-fw pi-pencil',
                'items' => [
                    [
                        'label' => 'Quản lí dịch vụ',
                        'icon'  => 'pi pi-fw pi-building',
                        'items' => [
                            [
                                'label' => 'Quản lý nhà cung cấp',
                                'icon'  => 'pi pi-fw pi-list',
                                'to' => route('admin.supplier.index'),
                                'active' => in_array($routeName, ['admin.supplier.index', 'admin.suppler.store', 'admin.suppler.update']),
                            ],
                            [
                                'label' => 'Quản lý sản phẩm',
                                'icon'  => 'pi pi-fw pi-bookmark',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Quản lí nhân viên',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Quản lý bàn',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Quản lý voucher',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Quản lí giờ chơi',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Quản lí hóa đơn',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => 'Quản lý order',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                ],
            ],
            [
                'label' => 'Báo cáo',
                'icon' => 'pi pi-fw pi-chart-bar',
            ],
        ];
        $breadcrumbs = [
            ['label' => 'Trang chủ', 'icon' => 'pi pi-home', 'url' => route('admin.dashboard.index')],
        ];
        if (in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update'])) {
            $breadcrumbs[] = ['label' => 'Danh sách người dùng', 'icon' => 'pi pi-list', 'url' => session()->get('admin.user.list')[0] ?? route('admin.user.index')];
            if (in_array($routeName, ['admin.user.edit', 'admin.user.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa người dùng', 'icon' => 'pi pi-user-edit', 'url' => route('admin.user.edit', $request->user)];
            }
            if (in_array($routeName, ['admin.user.create', 'admin.user.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm người dùng', 'icon' => 'pi pi-user-plus', 'url' => route('admin.user.create')];
            }
        }
        if (in_array($routeName, ['admin.supplier.index', 'admin.supplier.create', 'admin.supplier.store', 'admin.supplier.edit', 'admin.supplier.update'])) {
            $breadcrumbs[] = ['label' => 'Quản lí nhà cung cấp', 'icon' => 'pi pi-list', 'url' => route('admin.supplier.index')];
            if (in_array($routeName, ['admin.supplier.create', 'admin.supplier.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm nhà cung cấp', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.create')];
            }
            if (in_array($routeName, ['admin.supplier.edit', 'admin.supplier.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa nhà cung cấp', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.edit', $request->supplier)];
            }
        }
        if (in_array($routeName, ['admin.payment.index'])) {
            $breadcrumbs[] = ['label' => 'Điều tra・Nhập kim・Trả kim・Trễ hạn', 'icon' => 'pi pi-list', 'url' => route('admin.payment.index')];
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'dataSession' => $dataSession,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
