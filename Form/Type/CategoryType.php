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

namespace Plugin\ProductPriority\Form\Type;

use Eccube\Entity\Category;
use Eccube\Repository\CategoryRepository;
use Plugin\ProductPriority\Entity\ProductPriority;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends AbstractType
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepository;

    /**
     * CategoryType constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductPriorityRepository $productPriorityRepository
     */
    public function __construct(CategoryRepository $categoryRepository, ProductPriorityRepository $productPriorityRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->productPriorityRepository = $productPriorityRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Categories = $this->categoryRepository->getList(null, true);
        $countByCategory = $this->productPriorityRepository->getPriorityCountGroupByCategory();

        $emptyValue = sprintf(
            trans('product_priority.form.category.empty'),
            isset($countByCategory[ProductPriority::CATEGORY_ID_ALL_PRODUCT])
                ? $countByCategory[ProductPriority::CATEGORY_ID_ALL_PRODUCT]
                : 0
        );

        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => 'Eccube\Entity\Category',
                'choice_label' => function (Category $Category) use ($countByCategory) {
                    $id = $Category->getId();
                    $name = $Category->getNameWithLevel();
                    $count = isset($countByCategory[$id]) ? $countByCategory[$id] : 0;

                    return sprintf(trans('product_priority.form.category.format'), $name, $count);
                },
                'choices' => $Categories,
                'placeholder' => $emptyValue,
                'empty_data' => null,
                'required' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_product_priority_category';
    }
}
