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

namespace MdjamanCommon\EventManager;

use Zend\EventManager;
use Zend\ServiceManager;

/**
 * Trait EventManagerAwareTrait
 * @package MdjamanCommon\EventManager
 */
trait EventManagerAwareTrait
{
    use EventManager\EventManagerAwareTrait;

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * If the class implements ServiceManager, it adds the global dibber shared
     * event manager to it.
     *
     * @return EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManager\EventManagerInterface) {
            $this->setEventManager(new EventManager\EventManager());
            if ($this instanceof ServiceManager\ServiceLocatorInterface) {
                $this->getEventManager()->setSharedManager($this->getServiceManager()->get('mdjaman_event_manager'));
            }
        }
        return $this->events;
    }
}
