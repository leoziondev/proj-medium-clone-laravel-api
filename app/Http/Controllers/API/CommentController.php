<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $postId = $request->query("post_id");
        $comments = Comment::select("id", "user_id", "content", "created_at")
            ->with("user")
            ->where("post_id", $postId)
            ->get();

        return ["status" => 200, "comments" => $comments];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            "post_id"   => "required",
            "content"   => "required|max:10000"
        ]);

        $user = $request->user();
        $payload["user_id"] = $user->id;

        Comment::create($payload);
        return ["status" => 200, "message" => "Comment successfully!"];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::select("id", "user_id", "content")
            ->where("id", $id)
            ->get();

        return ["status" => 200, "comment" => $comment];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payload = $request->validate([
            "post_id"   => "required",
            "content"   => "required|max:10000"
        ]);

        $user = $request->user();
        $payload["user_id"] = $user->id;

        Comment::where("id", $id)->update($payload);
        return ["status" => 200, "message" => "Comment updated successfully!"];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $comment = Comment::find($id);

        $user = $request->user();

        if ($user->id !== $comment->user_id) {
            return response()->json(["status" => 401, "message" => "Unauthorized"], 401);
        }
        $comment->delete();

        return ["status" => 200, "message" => "Comment deleted successfuly!"];
    }
}
