<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'recommend');
        $keyword = $request->query('keyword');

        $query = Item::query();

        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        if ($tab === 'mylist') {
            if (!$user) {
                return redirect()->route('login');
            }

            $query->whereHas('likes', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return view('index', compact('items', 'tab', 'keyword'));
    }

    public function show($item_id)
    {
        $item = Item::with(['condition', 'categories', 'comments.user', 'likes', 'soldItem.user'])->findOrFail($item_id);
        $user = Auth::user();
        $categories = $item->categories;
        $trade = $item->soldItem;
        $canOpenTrade = $trade && $user && $trade->isParticipant($user->id);

        return view('item.show', compact('item', 'user', 'categories', 'trade', 'canOpenTrade'));
    }
}