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

use Zend\EventManager\ResponseCollection;

/**
 * Trait TriggerEventTrait
 * @package MdjamanCommon\EventManager
 */
trait TriggerEventTrait
{
    /**
     * Trigger an event more easily :
     * - $target is $this by default
     *
     * @param  string $event
     * @param  array|object $argv
     * @param  object|string $target
     * @param  null|callable $callback
     * @return ResponseCollection
     */
    public function triggerEvent($event, $argv = array(), $target = null, $callback = null)
    {
        if (! method_exists($this, 'getEventManager')) {
            throw new Exception\InvalidArgumentException(
                'MdjamanCommon\EventManager\TriggerEventTrait requires the class that uses it to implement 
                Zend\EventManager\EventManagerAwareInterface'
            );
        }

        if (is_null($target)) {
            $target = $this;
        }

        return $this->getEventManager()->trigger($event, $target, $argv, $callback);
    }
}
