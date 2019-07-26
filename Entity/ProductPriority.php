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

class ProductPriority extends AbstractEntity
{
    private $product_id;

    private $category_id;

    private $priority;

    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getProductId()
    {
        return $this->product_id;
    }

    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }
}
