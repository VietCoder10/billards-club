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
            [
                'label' => 'Hóa đơn',
                'icon' => 'pi pi-fw pi-file',
                'to' => route('user.invoice.index'),
                'active' => in_array($routeName, ['user.invoice.index', 'user.invoice.show']),
            ],
            [
                'label' => 'Đăng kí giải đấu',
                'icon' => 'pi pi-fw pi-trophy',
                'to' => route('user.tournament.index'),
                'active' => in_array($routeName, ['user.tournament.index']),
            ],
        ];

        $breadcrumbs = [
            ['label' => 'Trang chủ', 'icon' => 'pi pi-home', 'url' => route('user.dashboard.index')],
        ];
        if (in_array($routeName, ['user.invoice.index', 'user.invoice.show'])) {
            $breadcrumbs[] = ['label' => 'Hóa đơn', 'icon' => 'pi pi-fw pi-file', 'url' => route('user.invoice.index')];
            if (in_array($routeName, ['user.invoice.show'])) {
                $breadcrumbs[] = ['label' => 'Chi tiết hóa đơn', 'icon' => 'pi pi-fw pi-eye', 'url' => route('user.invoice.show', $request->invoice)];
            }
        }
        if (in_array($routeName, ['user.tournament.index', 'user.tournament.show'])) {
            $breadcrumbs[] = ['label' => 'Giải đấu', 'icon' => 'pi pi-fw pi-trophy', 'url' => route('user.tournament.index')];
            if (in_array($routeName, ['user.tournament.show'])) {
                $breadcrumbs[] = ['label' => 'Chi tiết giải đấu', 'icon' => 'pi pi-fw pi-eye', 'url' => route('user.tournament.show', $request->route('id') ?? $request->id)];
            }
        }
        if (in_array($routeName, ['user.table.index'])) {
            $breadcrumbs[] = ['label' => 'Tình trạng bàn', 'icon' => 'pi pi-fw pi-table', 'url' => route('user.table.index')];
        }
        if (in_array($routeName, ['user.profile.index'])) {
            $breadcrumbs[] = ['label' => 'Thông tin cá nhân', 'icon' => 'pi pi-fw pi-user', 'url' => route('user.profile.index')];
        }

        return array_merge(parent::share($request), [
            'routeName' => $routeName,
            'leftMenu' => $leftMenu,
            'breadcrumbs' => $breadcrumbs,
            'user' => \Auth::guard('customer')->user(),
            'session' => $dataSession,
        ]);
    }
}
