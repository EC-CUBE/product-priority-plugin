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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'order_by_id',
                'integer',
                array(
                    'label' => '商品ソートID',
                    'required' => false,
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\GreaterThan(
                            array(
                                'value' => 3,
                                'message' => '1～3はEC-CUBEで利用しています。4以上の値を設定してください',
                            )
                        ),
                    ),
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Plugin\ProductPriority\Entity\Config',
            )
        );
    }

    public function getName()
    {
        return 'admin_product_priority_config';
    }
}
