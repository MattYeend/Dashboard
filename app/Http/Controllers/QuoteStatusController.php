<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteStatusRequest;
use App\Http\Requests\UpdateQuoteStatusRequest;
use App\Models\QuoteStatus;

class QuoteStatusController extends Controller
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
    public function store(StoreQuoteStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(QuoteStatus $quoteStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QuoteStatus $quoteStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuoteStatusRequest $request, QuoteStatus $quoteStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuoteStatus $quoteStatus)
    {
        //
    }
}
