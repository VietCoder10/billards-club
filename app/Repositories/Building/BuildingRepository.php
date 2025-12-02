<?php

namespace App\Repositories\Building;

use App\Models\Building;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Components\CommonComponent;
use App\Http\Requests\Admin\Building\BuildingRequest;

class BuildingRepository implements BuildingInterface
{
    private Building $building;
    public function __construct(Building $building)
    {
        $this->building = $building;
    }

    public function get(Request $request): LengthAwarePaginator
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->building;
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('building_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('building_name_kana', $request['free_word']));
            });
        }
        $buildings = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($buildings)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $buildings = $builder->paginate($newSizeLimit);
        }

        return $buildings;
    }

    public function getById(string $id): ?Building
    {
        return $this->building->where('id', $id)->first();
    }

    public function store(BuildingRequest $request): bool
    {
        $building = new Building;
        $building->building_name = $request->building_name;
        $building->building_name_kana = $request->building_name_kana;
        $building->building_code = $request->building_code;
        $building->person_in_charge = $request->person_in_charge;
        $building->construction_reason = $request->construction_reason;
        $building->building_short_name = $request->building_short_name;

        return $building->save();
    }

    public function update(BuildingRequest $request, string $id): bool
    {
        $building = $this->getById($id);
        if (! $building) {
            return false;
        }
        $building->building_name = $request->building_name;
        $building->building_name_kana = $request->building_name_kana;
        $building->building_code = $request->building_code;
        $building->person_in_charge = $request->person_in_charge;
        $building->construction_reason = $request->construction_reason;
        $building->building_short_name = $request->building_short_name;

        return $building->save();
    }

    public function destroy(string $id): bool
    {
        $building = $this->getById($id);
        if (! $building) {
            return false;
        }
        return $building->delete();
    }

    public function checkCode(Request $request): bool
    {
        return ! $this->building->where(function ($query) use ($request) {
            if (isset($request['id'])) {
                $query->where('id', '!=', $request['id']);
            }
            $query->where(['building_code' => $request['value']]);
        })->exists();
    }
}
