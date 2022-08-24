<?php
/**
 * This file is part of the RIRGH project
 * Copyright (c) 2022 RIGRH
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @author Fabrys Sahiry <fsahiry@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace MdjamanCommon;

use Doctrine\Common\Persistence\ObjectManager;
use MdjamanCommon\EventListener\DoctrineExtensionsListener;
use MdjamanCommon\EventManager\DoctrineEvents;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Class Module
 * @package MdjamanCommon
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class Module implements
    ConfigProviderInterface, 
    BootstrapListenerInterface
{

    /**
     * @param EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $sm = $e->getTarget()->getServiceManager();

        $objectManager = null;

        if ($sm->has('mdjaman_auth_service')) {
            if ($sm->has('doctrine.entitymanager.orm_default')) {
                $objectManager = $sm->get('doctrine.entitymanager.orm_default');
            } elseif ($sm->has('doctrine.documentmanager.odm_default')) {
                $objectManager = $sm->get('doctrine.documentmanager.odm_default');
            }

            /* @var $objectManager ObjectManager */
            if ($objectManager instanceof ObjectManager) {
                $dem = $objectManager->getEventManager();
                $dem->addEventListener(
                    array(DoctrineEvents::PRE_FLUSH),
                    new DoctrineExtensionsListener($sm)
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
