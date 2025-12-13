<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(CommentRequest $request, $item_id)
    {
        $data = $request->validated();

        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
            'comment' => $data['comment'],
        ]);
        return back();
    }
}