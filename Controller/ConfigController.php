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

namespace Plugin\ProductPriority\Controller;

use Eccube\Controller\AbstractController;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Form\Type\ConfigType;
use Plugin\ProductPriority\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConfigController
 */
class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/config", name="product_priority_admin_config")
     * @Template("@ProductPriority/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->find(Config::ID);

        $builder = $this->formFactory
            ->createBuilder(ConfigType::class, $Config);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->flush($Config);

                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('product_priority_admin_config');
            } else {
                $this->addError('admin.common.save_error', 'admin');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
