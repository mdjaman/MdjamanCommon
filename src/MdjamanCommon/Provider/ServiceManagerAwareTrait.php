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

namespace MdjamanCommon\Provider;

use Zend\ServiceManager\ServiceManager;

/**
 * Trait ServiceManagerAwareTrait
 * @package MdjamanCommon\Provider
 */
trait ServiceManagerAwareTrait
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
    * Retrieve service manager instance
    *
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
    * Set service manager instance
    *
    * @param ServiceManager $serviceManager
    * @return ServiceManagerAwareTrait
    */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
