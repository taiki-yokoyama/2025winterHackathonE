# PDCA Spiral セットアップガイド

## クイックスタート

### 1. 環境変数の設定

```bash
cp .env.example .env
```

### 2. Dockerコンテナの起動

```bash
docker-compose up -d --build
```

### 3. Tailwind CSSのビルド（オプション）

```bash
npm install
npm run build
```

### 4. アプリケーションへのアクセス

- **アプリケーション**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MailHog**: http://localhost:8025

### 5. デモアカウントでログイン

- **ユーザー名**: demo
- **パスワード**: password

## 初回セットアップ後の確認事項

### データベースの確認

phpMyAdmin (http://localhost:8080) にアクセスして、以下のテーブルが作成されていることを確認：

- teams
- users
- pdca_cycles
- evaluations
- next_actions

### サンプルデータの確認

デモアカウントでログインして、以下が表示されることを確認：

- サイクル #1 が作成されている
- サンプル評価が2件表示される
- サンプルアクションが2件表示される

## トラブルシューティング

### ポート競合エラー

```bash
# ポート8080が使用中の場合
# docker-compose.ymlのポート番号を変更
ports:
  - "8090:80"  # 8080 → 8090に変更
```

### データベース接続エラー

```bash
# コンテナを再起動
docker-compose restart

# ログを確認
docker-compose logs db
```

### パーミッションエラー

```bash
# srcディレクトリの権限を変更
chmod -R 755 src/
```

### Tailwind CSSが反映されない

```bash
# CSSを再ビルド
npm run build

# または開発モード（ウォッチモード）
npm run dev
```

## 開発ワークフロー

### コンテナの起動・停止

```bash
# 起動
docker-compose up -d

# 停止
docker-compose down

# 再起動
docker-compose restart

# ログ確認
docker-compose logs -f
```

### データベースのリセット

```bash
# すべてのコンテナとボリュームを削除
docker-compose down -v

# 再起動（初期データが再作成される）
docker-compose up -d --build
```

### PHPコンテナ内でのコマンド実行

```bash
# PHPコンテナに入る
docker-compose exec php bash

# Composerパッケージのインストール
docker-compose exec php composer install
```

## 本番環境へのデプロイ

### 1. 環境変数の更新

`.env`ファイルを編集：

```env
DB_PASSWORD=強力なパスワード
SESSION_SECRET=ランダムな文字列
APP_ENV=production
```

### 2. 開発ツールの無効化

`docker-compose.yml`から以下を削除：

- phpMyAdmin
- MailHog

### 3. HTTPSの有効化

Nginxの設定でHTTPSを有効にし、`.htaccess`のHTTPSリダイレクトのコメントを解除。

### 4. セキュリティ設定

- `php.ini`で`display_errors = Off`に設定
- データベースのルートパスワードを変更
- ファイアウォールの設定

## 次のステップ

1. チームメンバーを招待して新規登録
2. 最初の評価を記録
3. ネクストアクションを追加
4. PDCAサイクルを回す

詳細は[README.md](README.md)を参照してください。
