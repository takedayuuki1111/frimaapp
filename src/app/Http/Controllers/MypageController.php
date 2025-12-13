<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use App\Models\SoldItem;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell'); 

        if ($page === 'buy') {
            $items = $user->soldItems()->with('item')->get()->pluck('item');
        } else {
            $items = $user->items()->orderBy('created_at', 'desc')->get();
        }

        return view('mypage.index', compact('user', 'items', 'page'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($request->hasFile('avatar_image')) {
            $path = $request->file('avatar_image')->store('avatars', 'public');
            $data['avatar_image'] = $path;
        }

        $user->update($data);

        return redirect()->route('mypage.index');
    }
}