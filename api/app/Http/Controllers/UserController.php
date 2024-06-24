<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:255',
            'new_password' => 'nullable|string|min:8|max:255',
        ]);

        $user = $request->user();

        // Check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('The provided password is incorrect.')], 422);
        }

        $user->email = $request->email;
        if($request->has('new_password') && !empty($request->new_password)){
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json(['message' => __('User updated successfully.')]);
    }
}
