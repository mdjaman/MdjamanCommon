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

namespace MdjamanCommon\Factory\Service;

use Interop\Container\ContainerInterface;
use MdjamanCommon\Service\LogEntryService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * Class LogEntryServiceFactory
 * @package MdjamanCommon\Factory\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class LogEntryServiceFactory implements FactoryInterface
{
    /**
     * Create LogEntryService
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $om = $container->has('doctrine.entitymanager.orm_default') ?
            $container->get('doctrine.entitymanager.orm_default') : $container->get('doctrine.documentmanager.odm_default');
        $options = $container->get('MdjamanCommon\Options\ModuleOptions');
        return new LogEntryService($container, $om, $options);
    }
}
