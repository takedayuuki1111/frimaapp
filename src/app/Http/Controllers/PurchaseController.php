<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\SoldItem;
use App\Http\Requests\AddressRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    public function create($item_id)
    {
        $item = Item::with('soldItem')->findOrFail($item_id);

        if ((int) $item->user_id === (int) Auth::id()) {
            return redirect()->route('index');
        }

        if ($item->isSold()) {
            $soldItem = $item->soldItem;

            if ($soldItem && ((int) $soldItem->user_id === (int) Auth::id() || (int) $item->user_id === (int) Auth::id())) {
                return redirect()->route('trade.show', $soldItem);
            }

            return redirect()->route('index');
        }

        $user = Auth::user();

        return view('purchase.create', compact('item', 'user'));
    }
    
    public function store(PurchaseRequest $request, $item_id)
    {
        $item = Item::with('soldItem')->findOrFail($item_id);

        if ((int) $item->user_id === (int) Auth::id() || $item->isSold()) {
            return redirect()->route('index');
        }

        if ($request->payment_method === 'card') {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['item_id' => $item->id]),
                'cancel_url' => route('purchase.cancel', ['item_id' => $item->id]),
            ]);

            return redirect($session->url);
        }

        if ($request->payment_method === 'konbini') {
            $soldItem = $this->processPurchase($item->id);

            return redirect()->route('trade.show', $soldItem);
        }

        return redirect()->route('index');
    }

    public function success($item_id)
    {
        $soldItem = $this->processPurchase($item_id);

        return redirect()->route('trade.show', $soldItem);
    }

    public function cancel($item_id)
    {
        return redirect()->route('purchase.create', ['item_id' => $item_id]);
    }

    private function processPurchase($item_id)
    {
        $soldItem = SoldItem::firstOrCreate(
            ['item_id' => $item_id],
            [
                'user_id' => Auth::id(),
                'status' => 'trading',
            ]
        );

        if (is_null($soldItem->status)) {
            $soldItem->update(['status' => 'trading']);
        }

        if ($soldItem->messages()->doesntExist()) {
            $soldItem->messages()->create([
                'user_id' => Auth::id(),
                'message' => '購入が完了しました。よろしくお願いします。',
            ]);
        }

        return $soldItem;
    }

    public function editAddress($item_id)
    {
        return view('purchase.address', compact('item_id'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $user = Auth::user();
    
        $user->update([
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building_name' => $request->building_name,
        ]);

        return redirect()->route('purchase.create', ['item_id' => $item_id]);
    }
}