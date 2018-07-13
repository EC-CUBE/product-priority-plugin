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

namespace Plugin\ProductPriority\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\ProductPriority\Entity\Config;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ConfigRepository
 */
class ConfigRepository extends AbstractRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Config::class);
    }
}
