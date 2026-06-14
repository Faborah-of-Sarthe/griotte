<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        return auth('sanctum')
            ->user()
            ->tags()
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where('user_id', $user->id),
            ],
        ]);

        return $user->tags()->create($validated);
    }

    public function update(Tag $tag, Request $request)
    {
        $user = auth('sanctum')->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where('user_id', $user->id)->ignore($tag->id),
            ],
        ]);

        $tag->fill($validated)->save();

        return $tag;
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json([
            'message' => 'Tag supprimé avec succès.'
        ], 200);
    }
}
