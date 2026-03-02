<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
use Inertia\Inertia;

class OrderController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('Admin/Order/Order', [
            'data' => [
                'title' => 'Danh sách bàn'
            ],
            'tables' => [
                ['id' => 1, 'name' => 'Bàn 01', 'status' => 'playing', 'playing_time' => '01:20'],
                ['id' => 2, 'name' => 'Bàn 02', 'status' => 'empty'],
                ['id' => 3, 'name' => 'Bàn 03', 'status' => 'playing', 'playing_time' => '00:45'],
                ['id' => 4, 'name' => 'Bàn 04', 'status' => 'empty'],
                ['id' => 5, 'name' => 'Bàn 05', 'status' => 'empty'],
                ['id' => 6, 'name' => 'Bàn 06', 'status' => 'playing', 'playing_time' => '02:10'],
                ['id' => 7, 'name' => 'Bàn 07', 'status' => 'empty'],
                ['id' => 8, 'name' => 'Bàn 08', 'status' => 'empty'],
                ['id' => 9, 'name' => 'Bàn 09', 'status' => 'empty'],
                ['id' => 10, 'name' => 'Bàn 10', 'status' => 'empty'],
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
