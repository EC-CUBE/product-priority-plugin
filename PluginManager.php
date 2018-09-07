<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductPriority;

use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Repository\ConfigRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginManager
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * @param array $config
     * @param ContainerInterface $container
     */
    public function enable(array $config, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        // mtb_product_list_order_byに"おすすめ順"を追加する.
        // idの最大値を取得
        $id = $entityManager->createQueryBuilder()
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
        $rank = $entityManager->createQueryBuilder()
            ->select('(COALESCE(MAX(plob.sort_no), 0) + 1) AS max_rank')
            ->from('Eccube\Entity\Master\ProductListOrderBy', 'plob')
            ->getQuery()
            ->getSingleScalarResult();

        // ソート順に追加
        $ProductListOrderBy = new ProductListOrderBy();
        $ProductListOrderBy->setId($id);
        $ProductListOrderBy->setName('おすすめ順');
        $ProductListOrderBy->setSortNo($rank);

        $entityManager->persist($ProductListOrderBy);
        $entityManager->flush($ProductListOrderBy);

        $Config = new Config();
        $Config->setId(Config::ID);
        $Config->setOrderById($id);

        $entityManager->persist($Config);
        $entityManager->flush($Config);
    }

    /**
     * @param array $config
     * @param ContainerInterface $container
     */
    public function disable(array $config, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        // "おすすめ順"を削除
        $Config = $container->get(ConfigRepository::class)
            ->find(Config::ID);

        $ProductListOrderBy = $entityManager
            ->getRepository('Eccube\Entity\Master\ProductListOrderBy')
            ->find($Config->getOrderById());

        if (!is_null($ProductListOrderBy)) {
            $entityManager->remove($ProductListOrderBy);
            $entityManager->flush($ProductListOrderBy);
        }
    }
}
