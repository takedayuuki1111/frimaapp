<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'recommend'); 
        $user = Auth::user();

        $query = Item::query();

        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }

        if ($tab === 'mylist' && $user) {
            $query->whereHas('likes', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return view('index', compact('items', 'tab'));
    }

    public function show($item_id)
    {
        $item = Item::with(['condition', 'categories', 'comments.user', 'likes'])->findOrFail($item_id);
        
        $categories = $item->categories;

        return view('item.show', compact('item', 'categories'));
    }
}