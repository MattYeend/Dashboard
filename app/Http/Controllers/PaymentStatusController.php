<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Models\PaymentStatus;

class PaymentStatusController extends Controller
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
    public function store(StorePaymentStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentStatus $paymentStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentStatus $paymentStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentStatusRequest $request, PaymentStatus $paymentStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentStatus $paymentStatus)
    {
        //
    }
}
