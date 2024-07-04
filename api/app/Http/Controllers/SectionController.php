<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Return all the sections of the authenticated user's current store
     */
    public function index()
    {

        $user = auth('sanctum')->user();
        $store = $user->currentStore;

        if ($store) {
            return $store->sections()->orderBy('order')->get();
        }

        else {
            return response()->json([
                'message' => __('No current store set for given user.')
            ], 404);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255|required',
            'color' => 'int|required',
            'icon' => 'string|required',
            'store_id' => 'int|required|exists:stores,id'
        ]);

        // Check if the store belongs to the authenticated user
        $user = auth('sanctum')->user();
        $store = $user->stores()->find($request->input('store_id'));


        if ($store) {
            // create the section
            $section = new Section;
            $section->name = $request->input('name');
            $section->color = $request->input('color');
            $section->icon = $request->input('icon');
            $section->store_id = $request->input('store_id');
            $section->save();

            return response()->json([
                'message' => __('Section created successfully.'),
                'section' => $section
            ], 201);
        }

        else {
            return response()->json([
                'message' => __('The given store does not belong to the authenticated user.')
            ], 403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'string|max:255|required',
            'color' => 'int|required',
            'icon' => 'string|required',
        ]);

        // update the section
        $section->fill($request->all());

        $section->save();

        return response()->json([
            'message' => __('Section updated successfully.'),
            'section' => $section
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json([
            'message' => __('Section deleted successfully.')
        ], 200);

    }
}
