<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Plugin\ProductPriority;

use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\ProductPriority\Entity\Config;

class PluginManager extends AbstractPluginManager
{
    public function install($config, $app)
    {
    }

    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);

        // mtb_product_list_order_byに"おすすめ順"を追加する.
        // idの最大値を取得
        $id = $app['orm.em']->createQueryBuilder()
            ->select('(COALESCE(MAX(plob.id), 0) + 1) AS max_id')
            ->from('Eccube\Entity\Master\ProductListOrderBy', 'plob')
            ->getQuery()
            ->getSingleScalarResult();
        // 3.0.12以降, 1～3までは本体で予約されているが、
        // データが存在しない場合もあるため、id = 3は使用しない
        if ($id == 3) {
            ++$id;
        }
        // rankの最大値を取得
        $rank = $app['orm.em']->createQueryBuilder()
            ->select('(COALESCE(MAX(plob.rank), 0) + 1) AS max_rank')
            ->from('Eccube\Entity\Master\ProductListOrderBy', 'plob')
            ->getQuery()
            ->getSingleScalarResult();

        // ソート順に追加
        $ProductListOrderBy = new ProductListOrderBy();
        $ProductListOrderBy->setId($id);
        $ProductListOrderBy->setName('おすすめ順');
        $ProductListOrderBy->setRank($rank);

        $app['orm.em']->persist($ProductListOrderBy);
        $app['orm.em']->flush($ProductListOrderBy);

        // 追加したIDを設定テーブルに保存
        $Config = $app['orm.em']
            ->getRepository('Plugin\ProductPriority\Entity\Config')
            ->find(Config::ID);

        if (is_null($Config)) {
            $Config = new Config();
            $Config->setId(Config::ID);
        }

        $Config->setOrderById($id);

        $app['orm.em']->persist($Config);
        $app['orm.em']->flush($Config);
    }

    public function disable($config, $app)
    {
        // "おすすめ順"を削除
        $Config = $app['orm.em']
            ->getRepository('Plugin\ProductPriority\Entity\Config')
            ->find(Config::ID);

        $ProductListOrderBy = $app['orm.em']
            ->getRepository('Eccube\Entity\Master\ProductListOrderBy')
            ->find($Config->getOrderById());

        if (!is_null($ProductListOrderBy)) {
            $app['orm.em']->remove($ProductListOrderBy);
            $app['orm.em']->flush($ProductListOrderBy);
        }
    }

    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }
}
