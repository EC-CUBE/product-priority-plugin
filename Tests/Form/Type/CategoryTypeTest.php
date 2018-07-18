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

namespace Plugin\ProductPriority\Tests\Form\Type;

use Eccube\Tests\Form\Type\AbstractTypeTestCase;
use Plugin\ProductPriority\Form\Type\CategoryType;

class CategoryTypeTest extends AbstractTypeTestCase
{
    /** @var \Symfony\Component\Form\FormInterface */
    protected $form;

    /** @var array デフォルト値（正常系）を設定 */
    protected $formData = [
        'category' => '1',
    ];

    public function setUp()
    {
        parent::setUp();

        // csrf tokenを無効にしてFormを作成
        $this->form = $this->formFactory
            ->createBuilder(
                CategoryType::class,
                null,
                [
                    'csrf_protection' => false,
                ]
            )
            ->getForm();
    }

    public function testValidData()
    {
        $this->form->submit($this->formData);

        $this->assertTrue($this->form->isValid());
    }
}
