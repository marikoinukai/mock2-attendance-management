# laravel-docker-template

## ダミーデータ

本アプリケーションでは、開発・動作確認用のダミーデータをSeederで作成しています。

### 作成されるユーザー

| 種別      | 名前    | メールアドレス                                       | パスワード    | メール認証 | 管理者権限 |
| ------- | ----- | --------------------------------------------- | -------- | ----- | ----- |
| 一般ユーザー  | ユーザー1 | [user1@example.com](mailto:user1@example.com) | password | 認証済み  | なし    |
| 一般ユーザー  | ユーザー2 | [user2@example.com](mailto:user2@example.com) | password | 認証済み  | なし    |
| 管理者ユーザー | ユーザー3 | [user3@example.com](mailto:user3@example.com) | password | 認証済み  | あり    |

管理者ユーザーは、`users` テーブルの `is_admin` カラムを `true` に設定しています。

### 作成される勤怠データ

全ユーザーに対して、勤怠記録と休憩記録のダミーデータを作成しています。

#### ユーザー1の勤怠データ

ユーザー1には、勤怠集計画面の確認用として、以下の意図的なデータを作成しています。

* 過去5ヶ月分：各月の平日15日分、合計75日分
* 当月分：17日分
* 合計：92日分
* 全勤怠に固定休憩 `12:00〜13:00` を付与

当月分の内訳は以下の通りです。

| 勤務パターン |  件数 | 勤務時間        |
| ------ | --: | ----------- |
| 通常勤務   | 10日 | 09:00〜18:00 |
| 残業     |  3日 | 09:00〜20:00 |
| 遅刻     |  2日 | 09:30〜18:00 |
| 早退     |  1日 | 09:00〜17:00 |
| 長時間労働  |  1日 | 08:00〜21:00 |

#### ユーザー2・ユーザー3の勤怠データ

ユーザー2とユーザー3には、画面表示確認用として、それぞれ30日分の勤怠データを作成しています。

* ユーザー2：30日分
* ユーザー3：30日分

### 勤怠集計画面の確認用データ

ユーザー1でログインし、`/attendance/report` を開いた場合、以下の値になる想定です。

| 項目               |   予測値 |
| ---------------- | ----: |
| 過去6ヶ月の総労働時間      | 744時間 |
| 過去6ヶ月の総残業時間      |  10時間 |
| 過去6ヶ月の平均労働時間 / 日 | 8時間5分 |
| 当月の遅刻回数          |    2回 |
| 当月の早退回数          |    1回 |
| 当月の長時間労働回数       |    1日 |

※ 残業時間は、1日の労働時間が8時間を超えた分で計算しています。
※ 長時間労働は、1日の労働時間が10時間を超えた日として判定しています。

### ダミーデータの作成方法

以下のコマンドで、データベースを作り直し、Seederでダミーデータを作成します。

```bash
docker compose exec php php artisan config:clear
docker compose exec php php artisan migrate:fresh --seed
```

`migrate:fresh` は既存のテーブルを削除して再作成するため、登録済みデータはすべて削除されます。
既存データを残したままSeederのみ実行したい場合は、以下を実行します。

```bash
docker compose exec php php artisan db:seed --class=DummyAttendanceSeeder
```


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
