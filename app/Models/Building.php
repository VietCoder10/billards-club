<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * Class Building
 *
 * @property int $id
 * @property string|null $building_name
 * @property string|null $building_name_kana
 * @property string|null $building_code
 * @property string|null $construction_company
 * @property string|null $person_in_charge
 * @property string|null $construction_reason
 * @property string|null $building_type
 * @property string|null $ownership_type
 * @property string|null $exclusive_area
 * @property int|null $building_age
 * @property bool $is_new
 * @property string|null $additional_items
 * @property string|null $postal_code_building
 * @property string|null $prefecture_building
 * @property string|null $city_building
 * @property string|null $town_building
 * @property string|null $block_building
 * @property string|null $building_room
 * @property string|null $remark_building
 * @property string|null $postal_code_contact
 * @property string|null $prefecture_contact
 * @property string|null $city_contact
 * @property string|null $town_contact
 * @property string|null $block_contact
 * @property string|null $contact_room
 * @property string|null $public_address
 * @property string|null $public_address_2
 * @property string|null $area
 * @property string|null $traffic
 * @property string|null $station1
 * @property string|null $station1_line
 * @property int|null $station1_bus_minutes
 * @property int|null $station1_walk_minutes
 * @property int|null $station1_bus_stop_minutes
 * @property int|null $station1_distance_km
 * @property string|null $station2
 * @property string|null $station2_line
 * @property int|null $station2_bus_minutes
 * @property int|null $station2_walk_minutes
 * @property int|null $station2_bus_stop_minutes
 * @property int|null $station2_distance_km
 * @property string|null $station3
 * @property string|null $station3_line
 * @property int|null $station3_bus_minutes
 * @property int|null $station3_walk_minutes
 * @property int|null $station3_bus_stop_minutes
 * @property int|null $station3_distance_km
 * @property int|null $total_units
 * @property int|null $floors_above
 * @property int|null $floors_below
 * @property int|null $parking_car
 * @property int|null $parking_bike
 * @property int|null $nearest_parking
 * @property Carbon|null $building_renewal_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Building extends Model
{
    use SoftDeletes;
    use Sortable;
    protected $table = 'buildings';

    protected $casts = [
        'building_age' => 'int',
        'is_new' => 'bool',
        'station1_bus_minutes' => 'int',
        'station1_walk_minutes' => 'int',
        'station1_bus_stop_minutes' => 'int',
        'station1_distance_km' => 'int',
        'station2_bus_minutes' => 'int',
        'station2_walk_minutes' => 'int',
        'station2_bus_stop_minutes' => 'int',
        'station2_distance_km' => 'int',
        'station3_bus_minutes' => 'int',
        'station3_walk_minutes' => 'int',
        'station3_bus_stop_minutes' => 'int',
        'station3_distance_km' => 'int',
        'total_units' => 'int',
        'floors_above' => 'int',
        'floors_below' => 'int',
        'parking_car' => 'int',
        'parking_bike' => 'int',
        'nearest_parking' => 'int',
        'building_renewal_date' => 'datetime'
    ];

    protected $fillable = [
        'building_name',
        'building_name_kana',
        'building_code',
        'construction_company',
        'person_in_charge',
        'construction_reason',
        'building_type',
        'ownership_type',
        'exclusive_area',
        'building_age',
        'is_new',
        'additional_items',
        'postal_code_building',
        'prefecture_building',
        'city_building',
        'town_building',
        'block_building',
        'building_room',
        'remark_building',
        'postal_code_contact',
        'prefecture_contact',
        'city_contact',
        'town_contact',
        'block_contact',
        'contact_room',
        'public_address',
        'public_address_2',
        'area',
        'traffic',
        'station1',
        'station1_line',
        'station1_bus_minutes',
        'station1_walk_minutes',
        'station1_bus_stop_minutes',
        'station1_distance_km',
        'station2',
        'station2_line',
        'station2_bus_minutes',
        'station2_walk_minutes',
        'station2_bus_stop_minutes',
        'station2_distance_km',
        'station3',
        'station3_line',
        'station3_bus_minutes',
        'station3_walk_minutes',
        'station3_bus_stop_minutes',
        'station3_distance_km',
        'total_units',
        'floors_above',
        'floors_below',
        'parking_car',
        'parking_bike',
        'nearest_parking',
        'building_renewal_date'
    ];
}
