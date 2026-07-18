<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceStatuses\StoreInvoiceStatusRequest;
use App\Http\Requests\InvoiceStatuses\UpdateInvoiceStatusRequest;
use App\Models\InvoiceStatus;

class InvoiceStatusController extends Controller
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
    public function store(StoreInvoiceStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceStatus $invoiceStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceStatus $invoiceStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceStatusRequest $request, InvoiceStatus $invoiceStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceStatus $invoiceStatus)
    {
        //
    }
}
