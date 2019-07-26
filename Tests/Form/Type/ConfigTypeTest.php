<?php
/*
  * This file is part of EC-CUBE
  *
  * Copyright (C) EC-CUBE CO.,LTD. All Rights Reserved.
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\ProductPriority\Tests\Form\Type;

use Eccube\Tests\EccubeTestCase;

class ConfigTypeTest extends EccubeTestCase
{
    /** @var \Symfony\Component\Form\FormInterface */
    protected $form;

    /** @var array デフォルト値（正常系）を設定 */
    protected $formData = array(
        'order_by_id' => '4',
    );

    public function setUp()
    {
        parent::setUp();

        // csrf tokenを無効にしてFormを作成
        $this->form = $this->app['form.factory']
            ->createBuilder(
                'admin_product_priority_config',
                null,
                array(
                    'csrf_protection' => false,
                )
            )
            ->getForm();
    }

    public function testValidData()
    {
        $this->form->submit($this->formData);

        $this->assertTrue($this->form->isValid());
    }

    public function testOrderByIdBlank()
    {
        $this->formData['order_by_id'] = '';
        $this->form->submit($this->formData);

        $this->assertFalse($this->form->isValid());
    }

    public function testOrderByIdGreaterThan()
    {
        $this->formData['order_by_id'] = '3';
        $this->form->submit($this->formData);

        $this->assertFalse($this->form->isValid());
    }
}
