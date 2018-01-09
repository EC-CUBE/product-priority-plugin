<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2016 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Plugin\ProductPriority\Controller;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Plugin\ProductPriority\Entity\Config;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends AbstractController
{
    public function index(Application $app, Request $request)
    {
        $Config = $app['eccube.plugin.product_priority.repository.config']
            ->find(Config::ID);

        $builder = $app['form.factory']
            ->createBuilder('admin_product_priority_config', $Config);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $app['orm.em']->flush($Config);

                $app->addSuccess('admin.register.complete', 'admin');

                return $app->redirect($app->url('plugin_ProductPriority_config'));
            } else {
                $app->addError('admin.register.failed', 'admin');
            }
        }

        return $app->render(
            'ProductPriority/Resource/template/admin/config.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}
