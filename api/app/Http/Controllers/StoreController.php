<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;

class StoreController extends Controller
{
    /**
     * Return a listing of the resource.
     * /!\ Not all stores, but only the stores belonging to the authenticated
     *
     * @return \Illuminate\Http\Response<\App\Models\Store>
     */
    public function index()
    {
        $user = User::find(1); // temporary - tests
        return $user->stores;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Switch the user's current store
     */
    public function update(Store $store)
    {
        $user = User::find(1); // temporary - tests
        $user->current_store = $store->id;
        $user->save();

        return response()->json([
            'message' => 'Current store updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
