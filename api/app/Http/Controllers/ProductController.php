<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = auth('sanctum')->user();
        $store = $user->currentStore;
        // $products = [];

        if ($store) {
            // get all products flagged as "to buy", categorised by the current store's sections
            $products = $store->sections()->orderBy('order')->with(['products' => function($query) {
                $query->where('to_buy', 1);
                $query->orderBy('updated_at', 'asc');
            }])->get();


            // add all products flagged as "to buy" and not belonging to any section
            $noSection = new Section;
            $noSection->id = 0;
            $noSection->name = 'Non classé';
            $noSection->color = 0;
            $noSection->icon = 'question';
            $noSection->products = Product::where('user_id', $user->id)
                                ->where('to_buy', 1)
                                ->where(function ($query) use ($store) {
                                    $query->doesntHave('sections') // Products with no section
                                        ->orWhereDoesntHave('sections', function ($innerQuery) use ($store) {
                                            // Products with sections that don't belong to the current store
                                            $innerQuery->whereIn('section_id', $store->sections()->pluck('id'));
                                        });
                                })
                                ->get();

            $products->push($noSection);
            return $products;
        }

        else {
            return response()->json([
                'message' => __('No current store set for given user. Please select a store.')
            ], 404);
        }
    }

    /**
     * Autocomplete products names
     */
    public function autocomplete(Request $request)
    {
        $products = Product::where('name', 'LIKE', '%' . $request->input('q') . '%')
                    ->where('user_id', auth('sanctum')->user()->id)
                    ->with([
                        'sections' => function($query) {
                            $query->where('store_id', auth('sanctum')->user()->currentStore->id);
                        }
                    ])
                    ->limit(7)
                    ->get();

        return $products->makeHidden('comment');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // get the authenticated user
        $user = auth('sanctum')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'to_buy' => 'boolean',
            'comment' => 'nullable|string',
            'section_id' => 'nullable|integer|exists:sections,id',
        ]);

        // Begin a transaction
        DB::transaction(function () use ($request, $user, &$product) {


            // create the product
            $product = Product::create([
                'name' => ucfirst($request->input('name')),
                'to_buy' => 1,
                'comment' => $request->input('comment'),
                'user_id' => $user->id,
            ]);

            // If the section_id is provided, attach the product to the given section
            if($request->input('section_id')) {
                $section = Section::find($request->input('section_id'));
                $section->products()->attach($product->id);
            }

            if(!$user->finished_tutorial) {
                $user->finished_tutorial = 1;
                $user->save();
            }

            return response()->json([
                'message' => __('Product created successfully.'),
                'product' => $product,
                'user' => $user
            ], 201);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $user = auth('sanctum')->user();

        $request->validate([
            'name' => 'string|max:255',
            'to_buy' => 'boolean',
            'comment' => 'nullable|string',
        ]);

        if($request->has('name')) {
            $request['name'] = ucfirst($request->input('name'));
        }

        // update the product
        $product->fill($request->all());


        // If the section_id is provided, attach the product to the given section and detach the product from all other sections of this store
        if($request->has('section_id')) {

            $user = auth('sanctum')->user();

            // Detach the product from all sections of this store
            $sections = $product->sections;
            foreach ($sections as $section) {
                // Check if the section belongs to the current store
                if ($section->store_id == $user->currentStore->id) {
                    // If yes, detach the product from this section
                    $section->products()->detach($product->id);
                }
            }

            // Attach the product to the given section if it is not the "no section" section
            if($request->input('section_id') != 0) {
                $product->sections()->attach($request->input('section_id'));
            }
        }

        // In case of checking, clear the comment
        if($request->input('to_buy') == 0) {
            $product->comment = null;
        }


        $product->save();

        if(!$user->finished_tutorial) {
            $user->finished_tutorial = 1;
            $user->save();
        }

        return response()->json([
            'message' => __('Product updated successfully.'),
            'product' => $product,
            'user' => $user
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
            'message' => __('Product deleted successfully.')
        ], 201);
    }

    /**
     * Attach the product to a section
     */
    public function attachToSection(Product $product, Section $section)
    {
        // Search for the given section
        if (!$section) {
            return response()->json([
                'message' => __('Section not found.')
            ], 404);
        } else if (!$product) {
            return response()->json([
                'message' => __('Product not found.')
            ], 404);
        } else {
            // Attach the product to the section
            $section->products()->attach($product->id);

            // Get the authenticated user
            $user = auth('sanctum')->user();

            // For all this products sections
            $sections = $product->sections;
            foreach ($sections as $section) {
                // Check if the section belongs to the current store
                if ($section->store_id == $user->currentStore->id) {
                    // If yes, detach the product from this section
                    $section->products()->detach($product->id);
                }
            }
        }

        return response()->json([
            'message' => __('Product attached successfully.'),
            'product' => $product
        ], 201);
    }
}
