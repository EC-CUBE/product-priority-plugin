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

namespace Plugin\ProductPriority\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Config
 *
 * @ORM\Table(name="plg_product_priority")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\ProductPriority\Repository\ProductPriorityRepository")
 */
class ProductPriority extends AbstractEntity
{
    const CATEGORY_ID_ALL_PRODUCT = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $product_id;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $category_id;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", options={"unsigned":true})
     */
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
