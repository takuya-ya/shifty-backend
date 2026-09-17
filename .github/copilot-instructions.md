# Shifty Backend AI Coding Instructions

## 言語

- **コードレビュー・コメント・チャットでの応答は必ず日本語で行うこと。** 英語での応答は禁止。

## このドキュメントについて

- GitHub Copilot（コードレビュー含む）が本リポジトリのコンテキストを理解しやすくするためのガイドです。
- 新しい機能を実装する際はここで示す技術選定・設計方針・モジュール構成を前提にしてください。

## アプリ概要

**Shifty** は、シフト作成作業を効率化し、管理者の負担を軽減するためのWebサービスです。半月ごとのシフト登録・編集・確定といった運用を一元化します。現在は **Phase1 (MVP)**。管理者機能を中心に構築中。

## 技術スタック

- **Framework**: Laravel 12 (PHP 8.2/8.5)
- **Database**: MySQL 8.4 (Docker Sail)
- **Caching/Queue**: Redis
- **Auth**: Laravel Sanctum (Cookie-based API 認証)
- **Code Quality**: PHP Strict Types, PSR-12 (Laravel Pint)
- **Testing**: PHPUnit (Unit / Feature Tests)

## プロジェクト構成と役割

```
app/
├── Http/
│   ├── Controllers/    # ルーティング入口、認可、Resource変換
│   └── Resources/      # APIレスポンス変換
├── Services/           # 業務ルール、トランザクション、横断ロジック
├── Repositories/       # DB操作(Eloquent/SQL)に特化、ビジネスロジックを持たない
└── Models/             # Eloquent モデル定義
```

## アーキテクチャ指針 (CSRパターン)

- **Controller**: 判断しない。リクエストを受取り、Serviceを呼び出し、Resourcesで返却するのみ。3行を超える処理は禁止。
- **Service**: 「業務の意味」を閉じ込める。
  - `QueryService`: 参照系（集計、フィルタリング）
  - `CommandService`: 操作系（作成、更新、削除）
  - `ConfirmService`: 状態遷移（確定処理）等
- **Repository**: 「どう取得・保存するか」のみを担当。Serviceからクエリビルダの詳細を隠蔽する。

## シフトドメインの仕様

- **期間概念**: 半月単位（1〜15日、16日〜末日）での管理。
- **状態遷移**: `draft` (下書き) → `confirmed` (確定)。
- **編集ロック**: `confirmed` 状態のシフトは原則として編集不可（PolicyやRequestで制限）。

## 認証フロー (Sanctum)

- **認証シーケンス**: ログイン前に `GET /sanctum/csrf-cookie` を呼び出し、次に `POST /api/login` を叩くフローを厳守。
- **認証状況判定**: ユーザー情報取得APIが成功するかどうかでログイン状態を判定。

## テスト戦略

- **テスト基準**: 「壊れたときに気づけるか × 影響範囲の大きさ」の2軸で判断。業務ルールに限らず、壊れたときに他機能に波及する共通処理もテスト対象。
- **Phase1**: 「気づきにくい×影響大」なエンドポイントのFeatureテスト。データ素通しのエンドポイントは認証＋正常系1本のみ。Service層・共通処理のUnitテスト。カバレッジ最低50%（結果指標）。

## アンチパターン

- **Controller内の肥大化**: ビジネスロジックを直接書かない。
- **Service内でのEloquent型依存**: `where`句などの詳細クエリをServiceに書かず、Repositoryに任せる。

## ファイル命名規則

- PSR-4 準拠。`ShiftService.php`, `ShiftRepository.php` (PascalCase)。

## コミットメッセージ規約

- コミットメッセージは日本語で、以下の形式：
  ```
  接頭辞: 本文
  - 理由の詳細（体言止め）
  ```
