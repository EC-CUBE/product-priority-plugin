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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ConfigType
 */
class ConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'order_by_id',
                IntegerType::class,
                [
                    'label' => 'product_priority.form.config.name',
                    'required' => false,
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\GreaterThan(
                            [
                                'value' => 3,
                                'message' => 'product_priority.form.config.valid_message',
                            ]
                        ),
                    ],
                ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Plugin\ProductPriority\Entity\Config',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_product_priority_config';
    }
}
