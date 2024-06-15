<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Store;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'copyFrom' => 'nullable|integer|exists:stores,id',
        ]);

        // Begin a transaction
        DB::transaction(function () use ($request, $user, &$store) {

            // create the store
            $store = Store::create([
                'name' => $request->input('name'),
                'user_id' => $user->id,
            ]);

            // If the copyFrom parameter is provided, copy the sections from the given store
            if($request->input('copyFrom')) {
                $storeToCopyFrom = Store::find($request->input('copyFrom'));
                $sectionsToCopy = $storeToCopyFrom->sections()->get();

                foreach ($sectionsToCopy as $sectionToCopy) {
                    $section = $sectionToCopy->replicate();
                    $section->store_id = $store->id;
                    $section->save();

                    $productsToCopy = $sectionToCopy->products()->get();

                    foreach ($productsToCopy as $productToCopy) {
                        $section->products()->attach($productToCopy->id);
                    }
                }
            }


        });
        return response()->json([
            'message' => __('Store created successfully.'),
            'store' => $store
        ], 201);

    }

    /**
     * Edit the store's name.
     */
    public function update(Request $request, Store $store)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $store->name = $request->input('name');
        $store->save();

        return response()->json([
            'message' => __('Current store updated successfully.')
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
            'message' => __('Current store updated successfully.')
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

        // Prevent delete if the store is the current store of the user
        $user = auth('sanctum')->user();
        if ($user->current_store == $store->id) {
            return response()->json([
                'message' => __('You cannot delete your current store.')
            ], 403);
        }


        $store->delete();

        return response()->json([
            'message' => __('Store deleted successfully.')
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
                    'message' => __('Section does not belong to the current store.')
                ], 403);
            }

            $section->order = $newOrder;
            $section->save();
        }

        return response()->json([
            'message' => __('Sections order updated successfully.')
        ], 200);
    }

}
