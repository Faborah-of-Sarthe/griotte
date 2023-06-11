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
            $noSection->name = 'Non classé';
            $noSection->color = 0;
            $noSection->icon = 0;
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
        // get the authenticated user
        $user = User::find(1); // TODO temporary - tests

        // create the product
        $product = Product::create([
            'name' => $request->input('name'),
            'to_buy' => 1, // TODO: will it always be true?
            'comment' => $request->input('comment'),
            'user_id' => $user->id,
        ]);

        // attach the product to the given section
        // TODO: should we allow to create a product without section?
        $section = Section::find($request->input('section_id'));
        if (!$section) {
            return response()->json([
                'message' => 'Section not found.'
            ], 404);
        } else {
            $section->products()->attach($product->id);
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // update the product
        $product->to_buy = $request->input('to_buy');
        $product->comment = $request->input('comment');
        $product->save();
        // no section management here (other endpoint)

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // delete the product
        $product = Product::find($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 201);
    }
}
