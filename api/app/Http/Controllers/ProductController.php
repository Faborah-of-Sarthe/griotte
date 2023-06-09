<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Return a listing of the specified resource.
     * Including its sections
     *
     */
    public function index()
    {
        // get the authenticated user
        $user = User::find(1); // TODO temporary - tests
        $store = $user->currentStore;
        // $products = [];

        if ($store) {
            // get all products flagged as "to buy", categorised by the current store's sections
            $products = $store->sections()->with(['products' => function($query) {
                $query->where('to_buy', 1);
            }])->get();
              
            // add all products flagged as "to buy" and not belonging to any section
            $noSection = new Section;
            $noSection->id = 0;
            $noSection->name = 'Unclassified';
            $noSection->products = Product::doesntHave('section')
                            ->where('user_id', $user->id)
                            ->where('to_buy', 1)
                            ->get();

            $products->push($noSection);
            return $products;
        }

        else {
            return response()->json([
                'message' => 'No current store set for given user.'
            ], 404);
        }
    }

    /**
     * Autocomplete products names
     */
    public function autocomplete(Request $request)
    {
        $products = Product::where('name', 'LIKE', '%' . $request->input('q') . '%')
        ->get();

        // load section for each product
        foreach ($products as $product) {
            $product->section;
        }

        return $products->makeHidden('comment');
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
