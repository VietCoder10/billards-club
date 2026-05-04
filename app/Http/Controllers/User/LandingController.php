<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\TablePriceMaster;
use App\Repositories\TablePriceMaster\TablePriceMasterInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends BaseController
{
    private $user;
    private TablePriceMasterInterface $tablePriceMaster;

    public function __construct(UserInterface $user, TablePriceMasterInterface $tablePriceMaster)
    {
        $this->user = $user;
        $this->tablePriceMaster = $tablePriceMaster;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request): Response|RedirectResponse
    {
        return Inertia::render('User/Landing/Index', [
            'data' => [
                'title' => 'landing',
                'tablePrices' => $this->tablePriceMaster->get($request),
            ],
        ]);
    }
}
