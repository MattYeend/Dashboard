<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailSuppressionRequest;
use App\Http\Requests\UpdateEmailSuppressionRequest;
use App\Models\EmailSuppression;

class EmailSuppressionController extends Controller
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
    public function store(StoreEmailSuppressionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailSuppression $emailSuppression)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailSuppression $emailSuppression)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmailSuppressionRequest $request, EmailSuppression $emailSuppression)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailSuppression $emailSuppression)
    {
        //
    }
}
