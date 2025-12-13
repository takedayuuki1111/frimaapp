# Coachtech フリマアプリ

## アプリケーション概要
Coachtechブランドのアイテムを出品・購入できるフリマアプリケーションです。
出品、購入、コメント、お気に入り登録、マイページ機能などを備えています。

## 使用技術（実行環境）
- **PHP**: 8.x
- **Framework**: Laravel 8.x / 9.x
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Infrastructure**: Docker / Docker Compose

### 環境構築手順

以下の手順でアプリケーションを起動してください。

### 1
```bash
git clone [リポジトリのURL]
cd [ディレクトリ名]

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