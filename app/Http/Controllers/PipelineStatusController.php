<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineStatusRequest;
use App\Http\Requests\UpdatePipelineStatusRequest;
use App\Models\PipelineStatus;

class PipelineStatusController extends Controller
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
    public function store(StorePipelineStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PipelineStatus $pipelineStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PipelineStatus $pipelineStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePipelineStatusRequest $request, PipelineStatus $pipelineStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PipelineStatus $pipelineStatus)
    {
        //
    }
}
