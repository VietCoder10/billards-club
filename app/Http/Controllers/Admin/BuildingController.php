<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Repositories\Building\BuildingInterface;
use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Http\Requests\Admin\Building\BuildingRequest;
use Carbon\Carbon;

class BuildingController extends BaseController
{
    private $building;

    public function __construct(BuildingInterface $building)
    {
        $this->building = $building;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $buildings = $this->building->get($request);
        session()->forget('admin.building.list');
        session()->push('admin.building.list', url()->full());

        return Inertia::render('Admin/Building/Index', $this->mergeSession([
            'data' => [
                'title' => '建物一覧',
                'buildings' => $buildings->items(),
                'sortLinks' => $this->sortLinks('admin.building.index', [
                    ['key' => 'building_code', 'name' => '建物コード'],
                    ['key' => 'building_name', 'name' => '建物名'],
                    ['key' => 'building_name_kana', 'name' => 'ふりがな'],
                    ['key' => 'building_short_name', 'name' => '建物名（省略可）'],
                    ['key' => 'person_in_charge', 'name' => '物件担当'],
                    ['key' => 'construction_reason', 'name' => '管理担当'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($buildings->appends(SearchQueryComponent::alterQuery($request))),
            ],
        ]));
    }

    public function create()
    {
        return Inertia::render('Admin/Building/Form', [
            'data' => [
                'title' => '建物新規登録',
                'urlBack' => session()->get('admin.building.list')[0] ?? route('admin.building.index'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BuildingRequest $request)
    {
        if (! $this->building->store($request)) {
            $this->setFlash(__('エラーが発生しました。'), 'error');
            return redirect()->route('admin.building.create');
        }
        $this->setFlash(__('建物を新規登録しました。'), 'success');

        return redirect()->route('admin.building.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $building = $this->building->getById($id);
        if (! $building) {
            $this->setFlash(__('エラーが発生しました。'), 'error');

            return redirect()->route('admin.building.index');
        }

        return Inertia::render('Admin/Building/Form', [
            'data' => [
                'title' => '建物登録の編集',
                'building' => $building,
                'isEdit' => true,
                'urlBack' => session()->get('admin.building.list')[0] ?? route('admin.building.index'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BuildingRequest $request, string $id)
    {
        $building = $this->building->getById($id);
        if (! $building) {
            $this->setFlash(__('エラーが発生しました。'), 'error');

            return redirect()->route('admin.building.index');
        }
        if (! $this->building->update($request, $id)) {
            $this->setFlash(__('エラーが発生しました。'), 'error');

            return redirect()->route('admin.building.edit', ['building' => $id]);
        }
        $this->setFlash(__('建物情報を更新しました。'), 'success');
        return redirect()->route('admin.building.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (! $this->building->destroy($id)) {
            return response()->json([
                'message' => 'エラーが発生しました。',
            ], StatusCode::INTERNAL_ERR);
        }
        return response()->json([
            'message' => '建物の削除が完了しました。',
        ], StatusCode::OK);

    }

    public function checkCode(Request $request)
    {
        return response()->json([
            'valid' => $this->building->checkCode($request),
        ], StatusCode::OK);
    }

    public function exportCsv(Request $request)
    {
        $buildings = $this->building->get($request);

        $buildings = $buildings->items();
        $header = [
            '建物コード',
            '建物名',
            'ふりがな',
            '建物名（省略可）',
            '物件担当',
            '管理担当',
        ];
        foreach ($header as $key => &$value) {
            $this->convertShiftJis($value);
        }
        $fileName = 'building_' . Carbon::now()->format('YmdHis') . '.csv';
        if (! file_exists(public_path() . '/csv_file')) {
            mkdir(public_path() . '/csv_file', 0777, true);
        }
        $localPath = public_path() . '/csv_file/' . $fileName;
        $file = fopen($localPath, 'w');
        fputcsv($file, $header);
        foreach ($buildings as $building) {
            $dataItem = [
                $this->convertShiftJis($building->building_code),
                $this->convertShiftJis($building->building_name),
                $this->convertShiftJis($building->building_name_kana),
                $this->convertShiftJis($building->building_short_name),
                $this->convertShiftJis($building->person_in_charge),
                $this->convertShiftJis($building->construction_reason),
            ];
            fputcsv($file, $dataItem);
        }
        return response()->json([
            'url' => url('/') . '/csv_file/' . $fileName,
            'name' => $fileName
        ], StatusCode::OK);
    }
}
