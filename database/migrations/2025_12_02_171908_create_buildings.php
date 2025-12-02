<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();

            // 基本情報
            $table->string('building_name')->nullable();          // 建物名（必須?）
            $table->string('building_name_kana')->nullable();     // ふりがな
            $table->string('building_short_name')->nullable();          // 建物名（必須?）
            $table->string('building_code')->nullable();          // 建物コード
            $table->string('construction_company')->nullable();   // 建設会社
            $table->string('person_in_charge')->nullable();       // 担当者
            $table->string('construction_reason')->nullable();    // 建物理由日（?）

            // 建物種別 & 所有種別
            $table->string('building_type')->nullable();          // 建物種別
            $table->string('ownership_type')->nullable();         // 所有種別
            $table->string('exclusive_area')->nullable();         // 一棟貸主物件？

            // 経年 & 新築
            $table->integer('building_age')->nullable();          // 経年数
            $table->boolean('is_new')->default(false);            // 新築フラグ

            // 追加物品
            $table->string('additional_items')->nullable();       // 追加物品

            // ---- 住所1（建物所在地） ----
            $table->string('postal_code_building')->nullable();   // 郵便番号
            $table->string('prefecture_building')->nullable();    // 都道府県
            $table->string('city_building')->nullable();          // 市区町村
            $table->string('town_building')->nullable();          // 丁目
            $table->string('block_building')->nullable();         // 番地
            $table->string('building_room')->nullable();          // 号
            $table->string('remark_building')->nullable();        // 備考

            // ---- 住所2（オーナー連絡用?） ----
            $table->string('postal_code_contact')->nullable();
            $table->string('prefecture_contact')->nullable();
            $table->string('city_contact')->nullable();
            $table->string('town_contact')->nullable();
            $table->string('block_contact')->nullable();
            $table->string('contact_room')->nullable();

            // 公開住所
            $table->string('public_address')->nullable();
            $table->string('public_address_2')->nullable();
            $table->string('area')->nullable();
            $table->string('traffic')->nullable();

            // ----- 沿線駅（駅1〜3） -----
            // 駅1
            $table->string('station1')->nullable();
            $table->string('station1_line')->nullable();
            $table->integer('station1_bus_minutes')->nullable();
            $table->integer('station1_walk_minutes')->nullable();
            $table->integer('station1_bus_stop_minutes')->nullable();
            $table->integer('station1_distance_km')->nullable();

            // 駅2
            $table->string('station2')->nullable();
            $table->string('station2_line')->nullable();
            $table->integer('station2_bus_minutes')->nullable();
            $table->integer('station2_walk_minutes')->nullable();
            $table->integer('station2_bus_stop_minutes')->nullable();
            $table->integer('station2_distance_km')->nullable();

            // 駅3
            $table->string('station3')->nullable();
            $table->string('station3_line')->nullable();
            $table->integer('station3_bus_minutes')->nullable();
            $table->integer('station3_walk_minutes')->nullable();
            $table->integer('station3_bus_stop_minutes')->nullable();
            $table->integer('station3_distance_km')->nullable();

            // ---- 特徴 ----
            $table->integer('total_units')->nullable();           // 総戸数
            $table->integer('floors_above')->nullable();          // 地上階数
            $table->integer('floors_below')->nullable();          // 地下階数
            $table->integer('parking_car')->nullable();           // 駐車場台数
            $table->integer('parking_bike')->nullable();          // 原付/バイク台数
            $table->integer('nearest_parking')->nullable();       // 近隣の駐車場
            $table->date('building_renewal_date')->nullable();    // 建物リニューアル日

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
