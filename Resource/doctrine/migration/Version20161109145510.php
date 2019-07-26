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

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;

class Version20161109145510 extends AbstractMigration
{
    protected $entities = array(
        'Plugin\ProductPriority\Entity\ProductPriority',
        'Plugin\ProductPriority\Entity\Config',
    );

    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array();
        foreach ($this->entities as $entity) {
            $classes[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);
    }

    public function down(Schema $schema)
    {
        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array();
        foreach ($this->entities as $entity) {
            $classes[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }
        $tool = new SchemaTool($em);
        $tool->dropSchema($classes);
    }
}
