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
            return $store->sections;
        }

        else {
            return response()->json([
                'message' => 'No current store set for given user.'
            ], 404);
        }

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
