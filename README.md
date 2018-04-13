# おすすめ順登録プラグイン(商品並べ替えプラグイン)

[![Build Status](https://travis-ci.org/EC-CUBE/product-priority-plugin.svg?branch=master)](https://travis-ci.org/EC-CUBE/product-priority-plugin)
[![Build status](https://ci.appveyor.com/api/projects/status/hyg4uc0mu49i3d3l?svg=true)](https://ci.appveyor.com/project/ECCUBE/product-priority-plugin)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7126b7f0-4e67-4aba-86cb-08d8214975fe/mini.png)](https://insight.sensiolabs.com/projects/7126b7f0-4e67-4aba-86cb-08d8214975fe)
[![Coverage Status](https://coveralls.io/repos/github/EC-CUBE/product-priority-plugin/badge.svg?branch=master)](https://coveralls.io/github/EC-CUBE/product-priority-plugin?branch=master)

## デモ

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

## 概要

- カテゴリ毎に、商品の並び順を任意に設定できる、おすすめ順ソートを追加します。
- EC-CUBE2系の商品並べ替えに相当する機能を提供します。

## 機能一覧

### フロント

- 商品一覧画面のソート順に、`おすすめ順`を追加します。
- `おすすめ順`を選択した場合、商品おすすめ順登録で登録した順に商品一覧が表示されます。

### 管理

- 商品管理＞商品おすすめ順登録メニューが追加されます。
- 商品おすすめ順登録メニューから、カテゴリ毎に商品の並び順を設定できます。

### その他

- デフォルトで追加するソート順の名称は`おすすめ順`です。
- 名称を変更したい場合は、システム設定＞マスターデータ管理から、`mtb_product_list_order_by`の値を変更してください。

## システム要件

- EC-CUBE 3.0.13以上
- PHP5.3/5.4/5.5/5.6/7.0/7.1
- PostgreSQL/MySQL
