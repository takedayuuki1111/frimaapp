<?php

namespace App\Mail;

use App\Models\SoldItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public SoldItem $soldItem;

    public function __construct(SoldItem $soldItem)
    {
        $this->soldItem = $soldItem->loadMissing(['item', 'buyer']);
    }

    public function build()
    {
        return $this->subject('【coachtechフリマ】取引完了のお知らせ')
            ->view('emails.trade_completed');
    }
}
