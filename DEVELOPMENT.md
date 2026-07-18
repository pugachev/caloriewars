# CalorieWars ローカル開発ガイド

ローカル(Docker)で動かすための手順まとめ。

## 構成

| サービス | コンテナ名 | ローカルURL/ポート |
|---|---|---|
| Laravel 10 + Apache (PHP 8.2) | caloriewars-app | http://localhost:8090/calorie |
| MySQL 8.0 | caloriewars-db | ホスト非公開(コンテナ間のみ) |
| phpMyAdmin | caloriewars-phpmyadmin | http://localhost:8082 |

- ベース定義は [docker-compose.yml](docker-compose.yml)(app=8080, db=3306)。
- ただし他プロジェクトとポートが競合するため、[docker-compose.override.yml](docker-compose.override.yml) で **appを8090に変更し、dbのホスト公開を停止** している(docker composeが自動で読み込む)。
- `.env` はリポジトリ直下のものがコンテナにマウントされる(`DB_HOST=caloriewars-db`)。`APP_URL` は 8080 のままだが動作に支障はない。

## 初回セットアップ

```bash
# 1. コンテナ起動
docker compose up -d

# 2. 依存パッケージのインストール(vendor/ はホストと共有される)
docker exec caloriewars-app composer install

# 3. DBの用意 — 下の「パターンA(本番データ復元)」または「パターンB(まっさら)」へ
```

起動確認: http://localhost:8090/calorie

## DBセットアップ

### パターンA: 本番ダンプから復元(推奨)

本番の全データ入りダンプは `database/dumps/` に置く(最新: `ikefuku40_caloriewars_20260718.sql`)。

```bash
# ダンプ投入(既存テーブルは DROP されず上書きINSERTされるため、作り直す場合は先に下記の初期化を)
docker exec -i caloriewars-db mysql -umtake -pManabu2010 caloriewars \
  < database/dumps/ikefuku40_caloriewars_20260718.sql

# マイグレーション履歴を最新化(ダンプに含まれない新規マイグレーションを記録する。
# 各マイグレーションは hasTable ガード付きなので既存テーブルはスキップされ安全)
docker exec caloriewars-app php artisan migrate
```

DBを完全に作り直したい場合:

```bash
docker compose down -v   # -v でDBボリューム(db_data)ごと削除
docker compose up -d
# → 上記のダンプ投入からやり直す
```

### パターンB: まっさらのDBから(マイグレーション+シーダー)

履歴データなしで、テーブルとマスタだけ作る場合:

```bash
docker exec caloriewars-app php artisan migrate        # 全テーブル作成
docker exec caloriewars-app php artisan db:seed        # カテゴリマスタ+サンプル摂取データ投入
```

作り直す場合は `php artisan migrate:fresh --seed`(全テーブルDROP→再作成→シード)。

## テーブルとマイグレーション

| テーブル | 用途 | マイグレーション |
|---|---|---|
| calories | 摂取熱量の記録(tgtcategory=106 は運動としてFitbit連携時代に使用) | 2023_02_12_102203 |
| categories | 摂取カテゴリマスタ(100:パン〜115:果物) | 2023_02_12_102204 |
| physical_categories | 運動カテゴリマスタ(200:歩行時間〜205:ステッパー) | 2023_02_12_102205 |
| physical_datas | 運動量・体重の記録 | 2023_02_12_102206 |

- すべてのマイグレーションは `Schema::hasTable()` でガードしており、**ダンプ復元後に `php artisan migrate` を流しても安全**(既存テーブルはスキップして履歴だけ記録される)。
- マスタのシーダー: `CategoriesTableSeeder` / `PhysicalCategoriesTableSeeder`(upsertなので何度実行してもOK)。

### 業務ロジックのメモ

- **確定摂取熱量** = 摂取合計 − (基礎代謝1450 + 確定運動量[204])。マイナスが良い状態。
- **ステッパー(205)を入力すると自動計算**: 歩行時間=値×1.5、歩数=時間×110、距離=歩数×0.7/1000、確定熱量=6.0×64×(値/60)×1.05([CalorieController::store_physical_info](app/Http/Controllers/CalorieController.php))。
- 週番号は「年初〜最初の日曜までを第0週」とする独自ルール(`getWeekOfYear`)。

## よく使うコマンド

```bash
docker compose up -d              # 起動
docker compose down               # 停止(データは残る)
docker compose down -v            # 停止+DB削除
docker exec caloriewars-app php artisan migrate:status   # マイグレーション状態確認
docker exec caloriewars-app php artisan view:clear       # Bladeキャッシュクリア
docker exec -it caloriewars-db mysql -umtake -pManabu2010 caloriewars  # MySQLに入る
docker logs caloriewars-app --tail 50                    # アプリログ
```

フロントアセット(CSS/JS)を変更した場合(Laravel Mix):

```bash
npm install
npm run dev      # 開発ビルド / npm run watch で監視
```

※ 一覧・グラフ画面は public/ 直下のビルド済みアセットとCDNを使っているため、Blade編集だけなら不要。

## 本番ダンプの更新手順

1. 本番のphpMyAdminからエクスポート(SQL形式)
2. `database/dumps/ikefuku40_caloriewars_YYYYMMDD.sql` として保存
3. 上記パターンAの手順で投入

## トラブルシューティング

- **ポートが競合する**: `docker-compose.override.yml` のポートを空いている番号に変更。
- **500エラー / Base table not found**: DBが空。パターンAまたはBを実施。
- **composerが無い/vendorが無い**: `docker exec caloriewars-app composer install`。
- **画面が古い**: `docker exec caloriewars-app php artisan view:clear`。
