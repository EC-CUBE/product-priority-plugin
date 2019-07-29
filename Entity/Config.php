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

namespace Plugin\ProductPriority\Entity;

use Eccube\Entity\AbstractEntity;

class Config extends AbstractEntity
{
    const ID = 1;

    private $id;

    private $order_by_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderById()
    {
        return $this->order_by_id;
    }

    public function setOrderById($order_by_id)
    {
        $this->order_by_id = $order_by_id;

        return $this;
    }
}
