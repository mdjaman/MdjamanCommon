<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2023 Marcel DJAMAN
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright 2023 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon;

use Doctrine\Persistence\ObjectManager;
use MdjamanCommon\EventListener\DoctrineExtensionsListener;
use MdjamanCommon\EventManager\DoctrineEvents;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

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
                    [DoctrineEvents::PRE_FLUSH],
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
