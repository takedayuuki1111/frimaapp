<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;

class LikeController extends Controller
{
    public function store($item_id)
    {
        $user = Auth::user();

        if (!$user->likes()->where('item_id', $item_id)->exists()) {
            Like::create([
                'user_id' => $user->id,
                'item_id' => $item_id,
            ]);
        }
        return back();
    }

    public function destroy($item_id)
    {
        $user = Auth::user();

        Like::where('user_id', $user->id)
            ->where('item_id', $item_id)
            ->delete();

        return back();
    }
}