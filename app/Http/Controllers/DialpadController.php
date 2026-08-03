<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlockedPhoneNumber;

class DialpadController extends Controller
{
    public function checkBlockedNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric'
        ]);

        $blocked = BlockedPhoneNumber::where(
            'phone_number',
            $request->phone_number
        )->exists();

        return response()->json([
            'blocked' => $blocked
        ]);
    }
}