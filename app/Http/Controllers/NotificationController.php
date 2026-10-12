<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Tandai semua notifikasi yang belum dibaca sebagai "sudah dibaca".
     */
    public function markAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return response()->json(['status' => 'success']);
    }
}