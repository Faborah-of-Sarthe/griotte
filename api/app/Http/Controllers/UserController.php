<?php

namespace App\Http\Controllers;

use App\Settings\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function update_settings(Request $request)
    {
        $donnees_validees = $request->validate(UserSettings::validationRules());

        $user = $request->user();
        $settings = $user->settings ?? UserSettings::fromArray(null);
        $settings = $settings->with($donnees_validees);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'settings' => json_encode($settings->toArray(), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => __('User settings updated successfully.'),
            'user' => $user->fresh(),
        ]);
    }
}
