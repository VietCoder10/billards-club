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
            // [
            //     'label' => '台帳登録',
            //     'icon' => 'pi pi-fw pi-pencil',
            //     'to' => route('admin.building.index'),
            //     'active' => in_array($routeName, ['admin.building.index', 'admin.building.create', 'admin.building.store', 'admin.user.edit', 'admin.user.update']),
            // ],
            [
                'label' => '台帳登録',
                'icon' => 'pi pi-fw pi-pencil',
                'active' => in_array($routeName, ['admin.building.index', 'admin.building.create', 'admin.building.store', 'admin.user.edit', 'admin.user.update']),
                'items' => [
                    [
                        'label' => '建物',
                        'icon'  => 'pi pi-fw pi-building',
                        'active' => in_array($routeName, ['admin.building.index', 'admin.building.create', 'admin.building.store', 'admin.user.edit', 'admin.user.update']),
                        'items' => [
                            [
                                'label' => '建物一覧',
                                'icon'  => 'pi pi-fw pi-list',
                                'to' => route('admin.building.index'),
                                'active' => in_array($routeName, ['admin.building.index', 'admin.building.create', 'admin.building.store', 'admin.user.edit', 'admin.user.update']),
                                // 'items' => [
                                //     ['label' => 'Submenu 1.1.1', 'icon' => 'pi pi-fw pi-bookmark', 'to' => route('admin.user.index'), 'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),],
                                //     ['label' => 'Submenu 1.1.2', 'icon' => 'pi pi-fw pi-bookmark', 'to' => route('admin.dashboard.index'),
                                //         'active' => in_array($routeName, ['admin.dashboard.index']),],
                                //     ['label' => 'Submenu 1.1.3', 'icon' => 'pi pi-fw pi-bookmark'],
                                // ],
                            ],
                            [
                                'label' => '部屋一覧',
                                'icon'  => 'pi pi-fw pi-bookmark',
                                // 'items' => [
                                //     ['label' => 'Submenu 1.2.1', 'icon' => 'pi pi-fw pi-bookmark'],
                                // ],
                            ],
                        ],
                    ],
                    [
                        'label' => '管理委託契約',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => '口座',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => '契約台帳',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                    [
                        'label' => '履歴の追加',
                        'icon'  => 'pi pi-fw pi-bookmark',
                    ],
                ],
            ],
            [
                'label' => '調査・入金・返金・延滞',
                'icon' => 'pi pi-fw pi-paypal',
                'to' => route('admin.payment.index'),
                'active' => in_array($routeName, ['admin.payment.index']),
            ],
            [
                'label' => 'EB（インターネットバンキング）',
                'icon' => 'pi pi-fw pi-arrow-right-arrow-left',
                'to' => route('admin.internet-banking.index'),
                'active' => in_array($routeName, ['admin.internet-banking.index']),
            ],
            [
                'label' => '集計',
                'icon' => 'pi pi-fw pi-chart-bar',
                'to' => route('admin.counting.index'),
                'active' => in_array($routeName, ['admin.counting.index']),
            ],
            [
                'label' => 'メンテナンス',
                'icon' => 'pi pi-fw pi-wrench',
                'to' => route('admin.maintenance.index'),
                'active' => in_array($routeName, ['admin.maintenance.index']),
            ],
            [
                'label' => '請求',
                'icon' => 'pi pi-fw pi-search',
                'to' => route('admin.billing.index'),
                'active' => in_array($routeName, ['admin.billing.index']),
            ],
            [
                'label' => '契約',
                'icon' => 'pi pi-fw pi-file-check',
                'to' => route('admin.contract.index'),
                'active' => in_array($routeName, ['admin.contract.index']),
            ],
            [
                'label' => '照会',
                'icon' => 'pi pi-fw pi-question-circle',
                'to' => route('admin.inquiry.index'),
                'active' => in_array($routeName, ['admin.inquiry.index']),
            ],
            [
                'label' => '会社',
                'icon' => 'pi pi-fw pi-building',
                'to' => route('admin.company.index'),
                'active' => in_array($routeName, ['admin.company.index']),
            ],
            [
                'label' => '送信依頼・受信情報',
                'icon' => 'pi pi-fw pi-send',
                'to' => route('admin.send-request.index'),
                'active' => in_array($routeName, ['admin.send-request.index']),
            ],
            [
                'label' => '設定',
                'icon' => 'pi pi-fw pi-cog',
                'to' => route('admin.setting.index'),
                'active' => in_array($routeName, ['admin.setting.index']),
            ],
            // [
            //     'label' => 'ユーザー一覧',
            //     'icon' => 'pi pi-fw pi-users',
            //     'to' => route('admin.user.index'),
            //     'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
            // ],
            // [
            //     'label' => 'Hierarchy',
            //     'icon' => 'pi pi-fw pi-align-justify',
            //     'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
            //     'items' => [
            //         [
            //             'label' => 'Submenu 1',
            //             'icon'  => 'pi pi-fw pi-bookmark',
            //             // 'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
            //             'items' => [
            //                 [
            //                     'label' => 'Submenu 1.1',
            //                     'icon'  => 'pi pi-fw pi-bookmark',
            //                     'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update', 'admin.dashboard.index']),
            //                     'items' => [
            //                         ['label' => 'Submenu 1.1.1', 'icon' => 'pi pi-fw pi-bookmark', 'to' => route('admin.user.index'), 'active' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),],
            //                         ['label' => 'Submenu 1.1.2', 'icon' => 'pi pi-fw pi-bookmark', 'to' => route('admin.dashboard.index'),
            //     'active' => in_array($routeName, ['admin.dashboard.index']),],
            //                         ['label' => 'Submenu 1.1.3', 'icon' => 'pi pi-fw pi-bookmark'],
            //                     ],
            //                 ],
            //                 [
            //                     'label' => 'Submenu 1.2',
            //                     'icon'  => 'pi pi-fw pi-bookmark',
            //                     'items' => [
            //                         ['label' => 'Submenu 1.2.1', 'icon' => 'pi pi-fw pi-bookmark'],
            //                     ],
            //                 ],
            //             ],
            //         ],
            //         [
            //             'label' => 'Submenu 2',
            //             'icon'  => 'pi pi-fw pi-bookmark',
            //             'items' => [
            //                 [
            //                     'label' => 'Submenu 2.1',
            //                     'icon'  => 'pi pi-fw pi-bookmark',
            //                     'items' => [
            //                         ['label' => 'Submenu 2.1.1', 'icon' => 'pi pi-fw pi-bookmark'],
            //                         ['label' => 'Submenu 2.1.2', 'icon' => 'pi pi-fw pi-bookmark'],
            //                     ],
            //                 ],
            //                 [
            //                     'label' => 'Submenu 2.2',
            //                     'icon'  => 'pi pi-fw pi-bookmark',
            //                     'items' => [
            //                         ['label' => 'Submenu 2.2.1', 'icon' => 'pi pi-fw pi-bookmark'],
            //                     ],
            //                 ],
            //             ],
            //         ],
            //     ],
            // ],
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
        if (in_array($routeName, ['admin.building.index', 'admin.building.create', 'admin.building.store', 'admin.user.edit', 'admin.user.update'])) {
            $breadcrumbs[] = ['label' => '建物一覧', 'icon' => 'pi pi-list', 'url' => route('admin.building.index')];
            if (in_array($routeName, ['admin.building.create', 'admin.building.store'])) {
                $breadcrumbs[] = ['label' => '建物新規登録', 'icon' => 'pi pi-pencil', 'url' => route('admin.building.create')];
            }
            if (in_array($routeName, ['admin.building.edit', 'admin.building.update'])) {
                $breadcrumbs[] = ['label' => '建物登録の編集', 'icon' => 'pi pi-pencil', 'url' => route('admin.building.edit', $request->building)];
            }
        }
        if (in_array($routeName, ['admin.payment.index'])) {
            $breadcrumbs[] = ['label' => '調査・入金・返金・延滞', 'icon' => 'pi pi-list', 'url' => route('admin.payment.index')];
        }
        if (in_array($routeName, ['admin.internet-banking.index'])) {
            $breadcrumbs[] = ['label' => 'EB（インターネットバンキング）', 'icon' => 'pi pi-list', 'url' => route('admin.internet-banking.index')];
        }
        if (in_array($routeName, ['admin.counting.index'])) {
            $breadcrumbs[] = ['label' => '集計', 'icon' => 'pi pi-list', 'url' => route('admin.counting.index')];
        }
        if (in_array($routeName, ['admin.maintenance.index'])) {
            $breadcrumbs[] = ['label' => 'メンテナンス', 'icon' => 'pi pi-list', 'url' => route('admin.maintenance.index')];
        }
        if (in_array($routeName, ['admin.billing.index'])) {
            $breadcrumbs[] = ['label' => '請求', 'icon' => 'pi pi-list', 'url' => route('admin.billing.index')];
        }
        if (in_array($routeName, ['admin.contract.index'])) {
            $breadcrumbs[] = ['label' => '契約', 'icon' => 'pi pi-list', 'url' => route('admin.contract.index')];
        }
        if (in_array($routeName, ['admin.inquiry.index'])) {
            $breadcrumbs[] = ['label' => '照会', 'icon' => 'pi pi-list', 'url' => route('admin.inquiry.index')];
        }
        if (in_array($routeName, ['admin.company.index'])) {
            $breadcrumbs[] = ['label' => '会社', 'icon' => 'pi pi-list', 'url' => route('admin.company.index')];
        }
        if (in_array($routeName, ['admin.send-request.index'])) {
            $breadcrumbs[] = ['label' => '送信依頼・受信情報', 'icon' => 'pi pi-list', 'url' => route('admin.send-request.index')];
        }
        if (in_array($routeName, ['admin.setting.index'])) {
            $breadcrumbs[] = ['label' => '設定', 'icon' => 'pi pi-list', 'url' => route('admin.setting.index')];
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'dataSession' => $dataSession,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
            // 'menuDashboardActive' => in_array($routeName, ['admin.dashboard.index']),
            // 'menuUserActive' => in_array($routeName, ['admin.user.index', 'admin.user.create', 'admin.user.edit', 'admin.user.store', 'admin.user.update']),
        ]);
    }
}
