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
        $user = User::find(1); // temporary - tests
        return $user->stores;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // create the store
        $store = new Store;
        $store->name = $request->input('name');
        $store->user_id = 1; // temporary - tests
        $store->save();
    }

    /**
     * Switch the user's current store
     * There is no classic update function, as the other informations are not editable
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
        $store = Store::find($id);
        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully.'
        ], 200);
    }

}
