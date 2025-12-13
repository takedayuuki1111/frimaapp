<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;

class SellController extends Controller
{
    // 出品画面表示
    public function create()
    {
        // 選択肢用のデータを取得
        $categories = Category::all();
        $conditions = Condition::all();

        return view('sell.create', compact('categories', 'conditions'));
    }

    // 商品出品処理
    public function store(ExhibitionRequest $request)
    {
        // 1. 画像の保存
        // storage/app/public/item_images に保存され、パスが返る
        $imagePath = $request->file('item_image')->store('item_images', 'public');

        // 2. 商品データの作成
        $item = Item::create([
            'user_id' => Auth::id(),
            'condition_id' => $request->condition_id,
            'name' => $request->name,
            'brand_name' => $request->brand_name, // 任意
            'price' => $request->price,
            'description' => $request->description,
            'img_url' => $imagePath, // 保存したパスをDBへ
        ]);

        // 3. カテゴリーの紐付け（中間テーブル）
        $item->categories()->sync($request->categories);

        // マイページへリダイレクト（仕様確認：または商品一覧へ）
        return redirect()->route('index');
    }
}