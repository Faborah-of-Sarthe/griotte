<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Client\Request;

class StoreController extends Controller
{
    /**
     * Return a listing of the resource.
     * /!\ Not all stores, but only the stores belonging to the authenticated user
     *
     * @return \Illuminate\Http\Response<\App\Models\Store>
     */
    public function index()
    {
        // get the authenticated user
        $user = auth('sanctum')->user();
        return $user->stores;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // get the authenticated user
        $user = auth('sanctum')->user();

        // create the store
        $store = new Store;
        $store->name = $request->input('name');
        $store->user_id = $user->id;
        $store->save();
    }

    /**
     * Edit the store's name.
     */
    public function update(Request $request, Store $store)
    {
        $store->name = $request->input('name');
        $store->save();

        return response()->json([
            'message' => 'Current store updated successfully.'
        ], 200);
    }

    /**
     * Switch the user's current store
     */
    public function updateCurrentStore(Store $store)
    {
        // get the authenticated user
        $user = auth('sanctum')->user();

        $user->current_store = $store->id;
        $user->save();

        return response()->json([
            'message' => 'Current store updated successfully.'
        ], 200);
    }

    /**
     * Return the current store for the current user
     */
    public function getCurrentStore()
    {
        // get the authenticated user
        $user = auth('sanctum')->user();

        return $user->current_store;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $store = Store::find($id);
        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully.'
        ], 200);
    }

}
