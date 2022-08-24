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

use Interop\Container\ContainerInterface;

/**
 * Trait ContainerAwareTrait
 * @package MdjamanCommon\Provider
 */
trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
    * Retrieve container instance
    *
    * @return ContainerInterface
    */
    public function getContainer()
    {
        return $this->container;
    }

    /**
    * Set service manager instance
    *
    * @param ContainerInterface $container
    * @return $this
    */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }
}
