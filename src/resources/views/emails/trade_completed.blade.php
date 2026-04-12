<p>{{ $soldItem->item->user->name ?? '出品者様' }} さん</p>

<p>商品「{{ $soldItem->item->name }}」の取引が完了しました。</p>
<p>購入者: {{ $soldItem->buyer->name }}</p>
<p>coachtechフリマにログインして内容をご確認ください。</p>
