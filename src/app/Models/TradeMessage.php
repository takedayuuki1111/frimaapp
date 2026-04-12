<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sold_item_id',
        'user_id',
        'message',
        'image_path',
    ];

    public function soldItem()
    {
        return $this->belongsTo(SoldItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
