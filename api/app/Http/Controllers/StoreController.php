<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\Section;
use Illuminate\Http\Request;


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
     * Return the specified store.
     */
    public function show(Store $store)
    {
        $store->sections = $store->sections()->orderBy('order')->get();
        return $store;
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
    public function destroy(Store $store)
    {
        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully.'
        ], 200);
    }

    /**
     * Change the order of the sections
     */
    public function updateSectionsOrder(Request $request, Store $store)
    {
        $sections = $request->input('sections');

        foreach ($sections as $section) {
            $newOrder = $section['order'];
            $section = Section::find($section['id']);

            // Check if the section belongs to the current store
            if ($section->store_id != $store->id) {
                return response()->json([
                    'message' => 'Section does not belong to the current store.'
                ], 403);
            }

            $section->order = $newOrder;
            $section->save();
        }

        return response()->json([
            'message' => 'Sections order updated successfully.'
        ], 200);
    }

}
