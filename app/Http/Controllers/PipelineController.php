<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;

class PipelineController extends Controller
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
    public function store(StorePipelineRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pipeline $pipeline)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pipeline $pipeline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePipelineRequest $request, Pipeline $pipeline)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pipeline $pipeline)
    {
        //
    }
}
