<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealStatusRequest;
use App\Http\Requests\UpdateDealStatusRequest;
use App\Models\DealStatus;

class DealStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreDealStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DealStatus $dealStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DealStatus $dealStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDealStatusRequest $request, DealStatus $dealStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DealStatus $dealStatus)
    {
        //
    }
}
