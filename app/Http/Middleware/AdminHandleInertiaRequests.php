<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


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
                'to' => route('admin.order.indexSession'),
                'active' => in_array($routeName, ['admin.order.indexSession', 'admin.order-item.edit']),
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
                                'icon'  => 'pi pi-fw pi-briefcase',
                                'to' => route('admin.supplier.index'),
                                'active' => in_array($routeName, ['admin.supplier.index', 'admin.supplier.store', 'admin.supplier.update']),
                            ],
                            [
                                'label' => 'Quản lý sản phẩm',
                                'icon'  => 'pi pi-fw pi-box',
                                'to' => route('admin.product.index'),
                                'active' => in_array($routeName, ['admin.product.index', 'admin.product.store', 'admin.product.update'])
                            ],
                        ],
                    ],
                    [
                        'label' => 'Quản lí nhân viên',
                        'icon' => 'pi pi-fw pi-users',
                        'to' => route('admin.user.index'),
                        'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.store', 'admin.user.edit', 'admin.user.update']),
                    ],
                    [
                        'label' => 'Quản lý bàn',
                        'icon'  => 'pi pi-fw pi-bookmark',
                        'to' => route('admin.table.index'),
                        'active' => in_array($routeName, ['admin.table.index', 'admin.table.create', 'admin.table.edit', 'admin.table.store', 'admin.table.update'])
                    ],
                    // [
                    //     'label' => 'Quản lý voucher',
                    //     'icon'  => 'pi pi-fw pi-bookmark',
                    // ],
                    [
                        'label' => 'Quản lí giá bàn',
                        'icon'  => 'pi pi-fw pi-clock',
                        'to' => route('admin.table-price-master.index'),
                        'active' => in_array($routeName, ['admin.table-price-master.index', 'admin.table-price-master.show', 'admin.table-price-master.create', 'admin.table-price-master.edit', 'admin.table-price-master.store', 'admin.table-price-master.update'])
                    ],
                    [
                        'label' => 'Quản lí hóa đơn',
                        'icon'  => 'pi pi-fw pi-bookmark',
                        'to' => route('admin.invoice.index'),
                        'active' => in_array($routeName, ['admin.invoice.index', 'admin.invoice.show', 'admin.invoice.create', 'admin.invoice.edit', 'admin.invoice.store', 'admin.invoice.update'])

                    ],
                    [
                        'label' => 'Quản lý đơn hàng',
                        'icon'  => 'pi pi-fw pi-bookmark',
                        'to' => route('admin.order.index'),
                        'active' => in_array($routeName, ['admin.order.index', 'admin.order.show', 'admin.order.edit', 'admin.order.store', 'admin.order.update'])
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
        if (in_array($routeName, ['admin.order.indexSession'])) {
            $breadcrumbs[] = ['label' => 'Phiên chơi', 'icon' => 'pi pi-play-circle', 'url' => session()->get('admin.order.list')[0] ?? route('admin.order.indexSession')];
            if (in_array($routeName, ['admin.order-item.edit', 'admin.order-item.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa đơn hàng', 'icon' => 'pi pi-user-edit', 'url' => route('admin.order.edit', $request->order)];
            }
        }

        if (in_array($routeName, ['admin.order.create', 'admin.order.edit', 'admin.order.store', 'admin.order.update'])) {
            $breadcrumbs[] = ['label' => 'Danh sách đơn hàng', 'icon' => 'pi pi-list', 'url' => session()->get('admin.order.list')[0] ?? route('admin.order.index')];
            if (in_array($routeName, ['admin.order.edit', 'admin.order.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa đơn hàng', 'icon' => 'pi pi-user-edit', 'url' => route('admin.order.edit', $request->order)];
            }
            if (in_array($routeName, ['admin.order.create', 'admin.order.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm đơn hàng', 'icon' => 'pi pi-user-plus', 'url' => route('admin.order.create')];
            }
        }
        if (in_array($routeName, ['admin.invoice.index', 'admin.invoice.create', 'admin.invoice.edit', 'admin.invoice.store', 'admin.invoice.update'])) {
            $breadcrumbs[] = ['label' => 'Danh sách hóa đơn', 'icon' => 'pi pi-list', 'url' => session()->get('admin.invoice.list')[0] ?? route('admin.invoice.index')];
            if (in_array($routeName, ['admin.invoice.edit', 'admin.invoice.update'])) {
                $breadcrumbs[] = ['label' => 'Chi tiết hóa đơn', 'icon' => 'pi pi-user-edit', 'url' => route('admin.invoice.edit', $request->invoice)];
            }
            // if (in_array($routeName, ['admin.invoice.create', 'admin.invoice.store'])) {
            //     $breadcrumbs[] = ['label' => 'Thêm đơn hàng', 'icon' => 'pi pi-user-plus', 'url' => route('admin.order.create')];
            // }
        }
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
            $breadcrumbs[] = ['label' => 'Quản lí nhà cung cấp', 'icon' => 'pi pi-fw pi-briefcase', 'url' => route('admin.supplier.index')];
            if (in_array($routeName, ['admin.supplier.create', 'admin.supplier.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm nhà cung cấp', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.create')];
            }
            if (in_array($routeName, ['admin.supplier.edit', 'admin.supplier.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa nhà cung cấp', 'icon' => 'pi pi-pencil', 'url' => route('admin.supplier.edit', $request->supplier)];
            }
        }
        if (in_array($routeName, ['admin.product.index', 'admin.product.create', 'admin.product.store', 'admin.product.edit', 'admin.product.update'])) {
            $breadcrumbs[] = ['label' => 'Quản lí sản phẩm', 'icon' => 'pi pi-fw pi-box', 'url' => route('admin.product.index')];
            if (in_array($routeName, ['admin.product.create', 'admin.product.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm sản phẩm', 'icon' => 'pi pi-pencil', 'url' => route('admin.product.create')];
            }
            if (in_array($routeName, ['admin.product.edit', 'admin.product.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa sản phẩm', 'icon' => 'pi pi-pencil', 'url' => route('admin.product.edit', $request->product)];
            }
        }
        if (in_array($routeName, ['admin.table-price-master.index', 'admin.table-price-master.create', 'admin.table-price-master.store', 'admin.table-price-master.edit', 'admin.table-price-master.update'])) {
            $breadcrumbs[] = ['label' => 'Quản lí giá bàn', 'icon' => 'pi pi-fw pi-clock', 'url' => route('admin.table-price-master.index')];
            if (in_array($routeName, ['admin.table-price-master.create', 'admin.table-price-master.store'])) {
                $breadcrumbs[] = ['label' => 'Thêm giá bàn', 'icon' => 'pi pi-pencil', 'url' => route('admin.table-price-master.create')];
            }
            if (in_array($routeName, ['admin.table-price-master.edit', 'admin.table-price-master.update'])) {
                $breadcrumbs[] = ['label' => 'Chỉnh sửa giá bàn', 'icon' => 'pi pi-pencil', 'url' => route('admin.table-price-master.edit', $request->tablePrice)];
            }
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'dataSession' => $dataSession,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
            'user' => Auth::guard('admin')->user(),
            
        ]);
    }
}
