<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use App\Models\SoldItem;

class MypageController extends Controller
{
    // プロフィール画面表示（出品・購入商品一覧）
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell'); // デフォルトは出品した商品

        if ($page === 'buy') {
            // 購入した商品（SoldItemsテーブル経由でItemsを取得）
            $items = $user->soldItems()->with('item')->get()->pluck('item');
        } else {
            // 出品した商品
            $items = $user->items()->orderBy('created_at', 'desc')->get();
        }

        return view('mypage.index', compact('user', 'items', 'page'));
    }

    // プロフィール編集画面表示
    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    // プロフィール更新処理
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        // 画像アップロード処理
        if ($request->hasFile('avatar_image')) {
            $path = $request->file('avatar_image')->store('avatars', 'public');
            $data['avatar_image'] = $path;
        }

        // ユーザー情報を更新
        $user->update($data);

        // マイページへリダイレクト
        return redirect()->route('mypage.index');
    }
}