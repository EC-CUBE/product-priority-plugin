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

namespace Plugin\ProductPriority\Form\Type;

use Eccube\Application;
use Eccube\Entity\Category;
use Plugin\ProductPriority\Constant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends AbstractType
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Categories = $this->app['eccube.repository.category']
            ->getList(null, true);

        $countByCategory = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->getPriorityCountGroupByCategory();

        $emptyValue = sprintf(
            '全ての商品(%s)',
            isset($countByCategory[Constant::CATEGORY_ID_ALL_PRODUCT])
                ? $countByCategory[Constant::CATEGORY_ID_ALL_PRODUCT]
                : 0
        );

        $builder->add(
            'category',
            'entity',
            array(
                'class' => 'Eccube\Entity\Category',
                'choice_label' => function (Category $Category) use ($countByCategory) {
                    $id = $Category->getId();
                    $name = $Category->getNameWithLevel();
                    $count = isset($countByCategory[$id]) ? $countByCategory[$id] : 0;

                    return sprintf('%s(%s)', $name, $count);
                },
                'choices' => $Categories,
                'empty_value' => $emptyValue,
                'empty_data' => null,
                'required' => false,
            )
        );
    }

    public function getName()
    {
        return 'admin_product_priority_category';
    }
}
