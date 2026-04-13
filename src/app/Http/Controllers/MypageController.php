<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\SoldItem;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell');

        if ($page === 'trading') {
            $page = 'trade';
        }

        $tradeRecords = SoldItem::with(['item.user', 'messages'])
            ->where(function ($query) use ($user) {
                $query->where('status', 'trading')
                    ->orWhere(function ($completedQuery) use ($user) {
                        $completedQuery->where('status', 'completed')
                            ->where(function ($pendingRatingQuery) use ($user) {
                                $pendingRatingQuery->where(function ($buyerQuery) use ($user) {
                                    $buyerQuery->where('user_id', $user->id)
                                        ->whereNull('seller_rating');
                                })->orWhere(function ($sellerQuery) use ($user) {
                                    $sellerQuery->whereNull('buyer_rating')
                                        ->whereHas('item', function ($itemQuery) use ($user) {
                                            $itemQuery->where('user_id', $user->id);
                                        });
                                });
                            });
                    });
            })
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($itemQuery) use ($user) {
                        $itemQuery->where('user_id', $user->id);
                    });
            })
            ->get()
            ->sortByDesc(fn ($trade) => $trade->latestMessageTimestampForUser($user->id))
            ->values();

        if ($page === 'buy') {
            $items = $user->soldItems()->with('item')->latest()->get()->pluck('item');
        } elseif ($page === 'trade') {
            $items = $tradeRecords->pluck('item');
        } else {
            $items = $user->items()->orderBy('created_at', 'desc')->get();
        }

        $sellCount = $user->items()->count();
        $buyCount = $user->soldItems()->count();
        $tradingCount = $tradeRecords->count();
        $tradesByItemId = $tradeRecords->keyBy('item_id');
        $unreadCountsByItemId = $tradeRecords->mapWithKeys(function ($trade) use ($user) {
            return [$trade->item_id => $trade->unreadCountForUser($user->id)];
        });
        $averageRating = $user->averageRating();
        $ratingCount = $user->ratingCount();

        return view('mypage.index', compact(
            'user',
            'items',
            'page',
            'sellCount',
            'buyCount',
            'tradingCount',
            'tradesByItemId',
            'unreadCountsByItemId',
            'averageRating',
            'ratingCount'
        ));
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