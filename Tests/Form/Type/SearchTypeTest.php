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

class SearchTypeTest extends EccubeTestCase
{
    /** @var \Symfony\Component\Form\FormInterface */
    protected $form;

    /** @var array デフォルト値（正常系）を設定 */
    protected $formData = array(
        'search' => '商品コード',
        'category_name' => 'カテゴリ名',
    );

    public function setUp()
    {
        parent::setUp();

        // csrf tokenを無効にしてFormを作成
        $this->form = $this->app['form.factory']
            ->createBuilder(
                'admin_product_priority_search',
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
}
