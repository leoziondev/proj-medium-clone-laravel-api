<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::select("id", "user_id", "title", "short_description", "created_at")->with("user")->get();

        return ["status" => 200, "posts" => $posts];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            "title"             => "required|min:5|max:191",
            "content"           => "nullable|max:20000",
            "short_description" => "min:5|max:250",
            "image"             => "nullable|image|mimes:png,jpg,jpeg,webp,gif,svg|max:2048"
        ]);

        try {
            $user = $request->user();
            $payload["user_id"] = $user->id;

            if ($payload["image"]) {
                $payload["image"] = $payload["image"]->store($user->id);
            }
            Post::create($payload);
            return ["status" => 200, "message" => "Post created successfully!"];
        } catch (\Exception $err) {
            Log::info("post_create_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::select("id", "user_id", "title", "short_description", "created_at")
            ->with("user")
            ->where("id", $id)
            ->first();

        return ["status" => 200, "posts" => $post];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payload = $request->validate([
            "title"             => "required|min:5|max:191",
            "content"           => "nullable|max:20000",
            "short_description" => "min:5|max:250",
            "image"             => "nullable|image|mimes:png,jpg,jpeg,webp,gif,svg|max:2048"
        ]);

        try {
            Post::where("id", $id)->update($payload);
            return ["status" => 200, "message" => "Post updated successfuly!"];
        } catch (\Exception $err) {
            Log::info("post_update_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $post = Post::find($id);
            $user = $request->user();

            if ($user->id !== $post->user_id) {
                return response()->json(["status" => 401, "message" => "Unauthorized"], 401);
            }

            if (isset($post->image)) {
                Storage::delete($post->image);
            }

            $post->delete();
            return ["status" => 200, "message" => "Post deleted successfuly!"];
        } catch (\Exception $err) {
            Log::info("post_delete_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }
}
