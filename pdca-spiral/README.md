# PDCA Spiral

チームの継続的改善サイクル（PDCA）を視覚的に追跡・管理するWebアプリケーション

## 概要

PDCA Spiralは、チームメンバーが定期的にチーム全体のパフォーマンスを評価し、振り返りを記録し、具体的なネクストアクションを決定できるツールです。螺旋階段のメタファーを用いて、繰り返しながら上昇する改善プロセスを視覚的に表現します。

## 主な機能

- 📊 **チーム評価**: 0-10のスコアで定期的にチームパフォーマンスを評価
- 💭 **振り返り記録**: 評価の原因や理由を言語化してもやもやを解消
- 🎯 **ネクストアクション**: 具体的な改善行動を決定・追跡
- 🌀 **螺旋可視化**: 評価履歴を螺旋階段のように美しく表示
- 🔄 **PDCAサイクル管理**: 継続的な改善サイクルを追跡
- 👥 **チーム管理**: チーム単位でデータを管理
- 🔐 **認証機能**: セキュアなログイン・ログアウト

## 技術スタック

- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Container**: Docker, Docker Compose
- **Web Server**: Nginx
- **Development Tools**: phpMyAdmin, MailHog

## 必要な環境

- Docker Desktop (または Docker Engine + Docker Compose)
- Git

## セットアップ手順

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd pdca-spiral
```

### 2. 環境変数の設定

```bash
cp .env.example .env
```

`.env`ファイルを編集して、必要に応じて設定を変更してください：

```env
DB_HOST=db
DB_NAME=pdca_spiral
DB_USER=root
DB_PASSWORD=root
SESSION_SECRET=your-random-secret-key
APP_ENV=development
```

### 3. Dockerコンテナの起動

```bash
# コンテナをビルド＆起動
docker-compose up -d --build

# 起動状態を確認
docker-compose ps
```

すべてのコンテナが正常に起動していることを確認してください。

### 4. データベースの初期化

初回起動時、`docker/mysql/init.sql`が自動的に実行され、必要なテーブルが作成されます。

### 5. アプリケーションへのアクセス

ブラウザで以下のURLにアクセスしてください：

- **アプリケーション**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MailHog** (メール確認): http://localhost:8025

## 使い方

### 初回利用

1. http://localhost:8080 にアクセス
2. 「新規登録」をクリック
3. ユーザー名、メールアドレス、パスワード、チーム名を入力
4. 登録完了後、自動的にログインされます

### 評価の記録

1. ダッシュボードから「評価を記録」をクリック
2. 0-10のスコアを選択
3. 評価の原因や理由を記入
4. 送信すると、螺旋グラフに反映されます

### ネクストアクションの設定

1. 「ネクストアクション」メニューをクリック
2. 具体的な改善行動を記入
3. 目標日を設定
4. 進捗に応じてステータスを更新

### PDCAサイクルの完了

1. ダッシュボードで「サイクルを完了」をクリック
2. 新しいサイクルが自動的に開始されます
3. 過去のサイクルは履歴として保存されます

## 開発

### ディレクトリ構造

```
pdca-spiral/
├── docker/                 # Docker設定ファイル
│   ├── nginx/             # Nginx設定
│   ├── php/               # PHP設定
│   └── mysql/             # MySQL設定・初期化スクリプト
├── src/                   # アプリケーションソースコード
│   ├── assets/            # CSS, JavaScript, 画像
│   ├── config/            # 設定ファイル
│   ├── controllers/       # コントローラー
│   ├── models/            # データモデル
│   ├── services/          # ビジネスロジック
│   ├── views/             # ビューテンプレート
│   ├── utils/             # ユーティリティ
│   └── index.php          # エントリーポイント
├── tests/                 # テストファイル
├── docker-compose.yml     # Docker Compose設定
├── package.json           # Node.js依存関係
├── tailwind.config.js     # Tailwind CSS設定
└── README.md
```

### Tailwind CSSのビルド

```bash
# 開発モード（ウォッチモード）
npm run dev

# 本番ビルド
npm run build
```

### ログの確認

```bash
# すべてのコンテナのログを表示
docker-compose logs -f

# 特定のコンテナのログを表示
docker-compose logs -f web
docker-compose logs -f php
docker-compose logs -f db
```

### データベースの操作

phpMyAdminを使用する場合：
1. http://localhost:8081 にアクセス
2. ユーザー名: `root`
3. パスワード: `root`

コマンドラインから直接アクセスする場合：
```bash
docker-compose exec db mysql -u root -proot pdca_spiral
```

### コンテナ内でのコマンド実行

```bash
# PHPコンテナに入る
docker-compose exec php bash

# Composerパッケージのインストール
docker-compose exec php composer install

# データベースコンテナに入る
docker-compose exec db bash
```

## テスト

### PHPUnitテストの実行

```bash
# すべてのテストを実行
docker-compose exec php vendor/bin/phpunit

# 特定のテストファイルを実行
docker-compose exec php vendor/bin/phpunit tests/UserTest.php
```

### プロパティベーステストの実行

```bash
# Erisを使用したプロパティテスト
docker-compose exec php vendor/bin/phpunit --group property
```

## トラブルシューティング

### ポートが既に使用されている

エラー: `Bind for 0.0.0.0:8080 failed: port is already allocated`

**解決方法**: `docker-compose.yml`のポート番号を変更してください。

```yaml
ports:
  - "8090:80"  # 8080 → 8090に変更
```

### データベース接続エラー

**解決方法**:
1. データベースコンテナが起動しているか確認
   ```bash
   docker-compose ps
   ```
2. `.env`ファイルの設定を確認
3. コンテナを再起動
   ```bash
   docker-compose restart
   ```

### パーミッションエラー

**解決方法**:
```bash
# srcディレクトリの権限を変更
chmod -R 755 src/
```

### コンテナが起動しない

**解決方法**:
```bash
# すべてのコンテナを停止・削除
docker-compose down

# ボリュームも削除して再起動
docker-compose down -v
docker-compose up -d --build
```

## コマンドリファレンス

```bash
# コンテナの起動
docker-compose up -d

# コンテナの停止
docker-compose down

# コンテナの再起動
docker-compose restart

# コンテナの状態確認
docker-compose ps

# ログの確認
docker-compose logs -f

# コンテナの再ビルド
docker-compose up -d --build

# 特定のコンテナだけ再起動
docker-compose restart web

# すべてのコンテナとボリュームを削除
docker-compose down -v
```

## セキュリティ

### 本番環境での注意事項

1. **環境変数の変更**
   - `SESSION_SECRET`を強力なランダム文字列に変更
   - `DB_PASSWORD`を強力なパスワードに変更

2. **開発ツールの無効化**
   - phpMyAdminとMailHogを本番環境では削除

3. **HTTPS の使用**
   - 本番環境では必ずHTTPSを使用

4. **定期的なアップデート**
   - Dockerイメージとパッケージを定期的に更新

## ライセンス

MIT License

## 貢献

プルリクエストを歓迎します！大きな変更の場合は、まずissueを開いて変更内容を議論してください。

## サポート

問題が発生した場合は、GitHubのissueを作成してください。

---

**開発チーム**: PDCA Spiral Team  
**バージョン**: 1.0.0  
**最終更新**: 2025年12月
