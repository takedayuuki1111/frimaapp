<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

Route::get('/', [ItemController::class, 'index'])->name('index');

Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('purchase')->group(function () {
        Route::get('/{item_id}', [PurchaseController::class, 'create'])->name('purchase.create');
        Route::post('/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');
        
        Route::get('/checkout/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
        Route::get('/checkout/cancel/{item_id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

        Route::get('/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
        Route::post('/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    });

    Route::prefix('sell')->group(function () {
        Route::get('/', [SellController::class, 'create'])->name('sell.create');
        Route::post('/', [SellController::class, 'store'])->name('sell.store');
    });

    Route::prefix('mypage')->group(function () {
        Route::get('/', [MypageController::class, 'index'])->name('mypage.index');
        
        Route::get('/profile', [MypageController::class, 'edit'])->name('mypage.edit');
        Route::post('/profile', [MypageController::class, 'update'])->name('mypage.update');
    });

    Route::post('/item/{item_id}/like', [LikeController::class, 'store'])->name('item.like');
    Route::delete('/item/{item_id}/like', [LikeController::class, 'destroy'])->name('item.unlike');
    Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])->name('item.comment.store');
});