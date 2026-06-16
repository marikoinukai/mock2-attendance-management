# laravel-docker-template



## PHPUnitテスト

本アプリケーションでは、テスト用データベースを使用してPHPUnitテストを実行します。
通常の開発用データベースとは別に、`attendance_test` をテスト用データベースとして使用します。

### テスト用データベースの作成

MySQLコンテナにログインします。

```bash
docker compose exec mysql mysql -u root -p
```

パスワードを求められた場合は、`root` を入力してください。

MySQLにログイン後、以下を実行します。

```sql
CREATE DATABASE IF NOT EXISTS attendance_test;
SHOW DATABASES;
exit;
```

### テスト用環境ファイル

テスト実行時は、`src/.env.testing` を使用します。
主な設定は以下の通りです。

```env
APP_ENV=testing
DB_CONNECTION=mysql_test
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=attendance_test
DB_USERNAME=root
DB_PASSWORD=root
```

また、`src/config/database.php` に `mysql_test` 接続設定を追加しています。

### テスト用テーブルの作成

以下のコマンドで、テスト用データベースにテーブルを作成します。

```bash
docker compose exec php php artisan config:clear
docker compose exec php php artisan migrate --env=testing
```

### テスト実行

全テストを実行する場合は、以下のコマンドを実行します。

```bash
docker compose exec php php artisan test
```

特定のテストクラスのみ実行する場合は、`--filter` を使用します。

```bash
docker compose exec php php artisan test --filter=RegisterTest
docker compose exec php php artisan test --filter=LoginTest
docker compose exec php php artisan test --filter=AttendanceStampTest
```

### 作成済みテスト

本アプリケーションでは、以下のFeatureテストを作成しています。

* 会員登録テスト
* 一般ユーザーログインテスト
* 管理者ログインテスト
* 勤怠画面の日時表示・ステータス表示テスト
* 出勤・休憩・退勤テスト
* 一般ユーザー勤怠一覧テスト
* 一般ユーザー勤怠詳細テスト
* 勤怠修正申請テスト
* 管理者勤怠一覧テスト
* 管理者勤怠詳細・修正テスト
* スタッフ一覧テスト
* スタッフ別勤怠一覧・CSV出力テスト

テストは主に `src/tests/Feature` 配下に配置しています。

### 補足

テストでは `RefreshDatabase` を使用しているため、各テストはテスト用データベースをリフレッシュしながら実行されます。
そのため、通常の開発用データベース `laravel_db` のデータには影響しません。
