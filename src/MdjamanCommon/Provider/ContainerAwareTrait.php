<?php

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
