<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\SoldItem;
use App\Http\Requests\AddressRequest; 

class PurchaseController extends Controller
{
    public function create($item_id)
    {
        $item = Item::findOrFail($item_id);

        if ($item->user_id === Auth::id()) {
            return redirect()->route('index'); 
        }

        if ($item->isSold()) {
            return redirect()->route('index'); 
        }

        $user = Auth::user();

        return view('purchase.create', compact('item', 'user'));
    }

    public function store(PurchaseRequest $request, $item_id)
    {
        SoldItem::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
        ]);
        return redirect()->route('index');
    }

    // 住所変更画面表示
    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);
        // 商品IDを渡して、更新後に戻れるようにする
        return view('purchase.address', compact('item_id'));
    }

    // 住所変更処理
    public function updateAddress(AddressRequest $request, $item_id)
    {
        $user = Auth::user();
        
        // ユーザーの住所情報を更新
        $user->update([
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building_name' => $request->building_name,
        ]);

        // 商品購入画面へ戻る
        return redirect()->route('purchase.create', ['item_id' => $item_id]);
    }
}