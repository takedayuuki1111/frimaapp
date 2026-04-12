# Coachtech フリマアプリ

## アプリケーション概要
Coachtechブランドのアイテムを出品・購入できるフリマアプリケーションです。
出品、購入、コメント、お気に入り登録、マイページ機能などを備えています。

## 使用技術（実行環境）
**PHP**: 8.x
**Framework**: Laravel 8.x
**Database**: MySQL 8.0
**Web Server**: Nginx
**Infrastructure**: Docker / Docker Compose
**Payment Processing**: Stripe API

### 環境構築手順

以下の手順でアプリケーションを起動してください。

### 1
```bash
git clone git@github.com:takedayuuki1111/frimaapp.git
cd frimaapp

### 2
cd src
cp .env.example .env

DB_HOST=mysql

### 3
cd ..
docker-compose up -d --build

### 4
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan storage:link
docker-compose exec php php artisan migrate:fresh --seed

### (5)
docker-compose exec php chmod -R 777 storage bootstrap/cache public

## 画面,URL,備考
アプリトップ,http://localhost/,
メール確認 (MailHog),http://localhost:8025/,メール認証や通知の確認用
phpMyAdmin,http://localhost:8080/,DB確認用 (user: laravel_user / pass: laravel_pass)

## テストアカウント
ユーザー名,メールアドレス,パスワード
テストユーザー,test@example.com,password123
coachtech太郎,taro@example.com,password123
サンプル花子,hanako@example.com,password123

## ダミーデータ
`docker-compose exec php php artisan migrate:fresh --seed` 実行後、以下の確認用データが入ります。

### テストアカウント
- `test@example.com` / `password123`
- `taro@example.com` / `password123`
- `hanako@example.com` / `password123`

### 仕様書準拠の商品データ
- 腕時計 / 15000円 / 良好
- HDD / 5000円 / 目立った傷や汚れなし
- 玉ねぎ3束 / 300円 / やや傷や汚れあり
- 革靴 / 4000円 / 状態が悪い
- ノートPC / 45000円 / 良好
- マイク / 8000円 / 目立った傷や汚れなし
- ショルダーバッグ / 3500円 / やや傷や汚れあり
- タンブラー / 500円 / 状態が悪い
- コーヒーミル / 4000円 / 良好
- メイクセット / 2500円 / 目立った傷や汚れなし

### 取引確認用データ
- `test@example.com`
  - 出品した商品あり
  - 購入した商品あり
  - `取引中の商品` タブに表示される取引データあり
  - 評価データあり
- `taro@example.com`
  - 出品・購入・評価済み取引データあり
- 取引チャット画面
  - サンプルメッセージを複数件登録済み

4/12 取引評価機能追加