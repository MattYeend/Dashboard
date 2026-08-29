<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notifications\StoreNotificationBroadcastRequest;
use App\Http\Requests\Notifications\UpdateNotificationBroadcastRequest;
use App\Models\NotificationBroadcast;

class NotificationBroadcastController extends Controller
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
    public function store(StoreNotificationBroadcastRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationBroadcast $notificationBroadcast)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NotificationBroadcast $notificationBroadcast)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationBroadcastRequest $request, NotificationBroadcast $notificationBroadcast)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationBroadcast $notificationBroadcast)
    {
        //
    }
}
