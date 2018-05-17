<?php
/**
 *
 * The MIT License (MIT)

 * Copyright (c) 2015 Marcel Djaman

 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.

 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright 2015 Marcel Djaman
 * @license http://www.opensource.org/licenses/MIT MIT License
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
            throw new Exception\InvalidArgumentException('MdjamanCommon\EventManager\TriggerEventTrait requires the class that uses it to implement Zend\EventManager\EventManagerAwareInterface');
        }

        if (is_null($target)) {
            $target = $this;
        }

        return $this->getEventManager()->trigger($event, $target, $argv, $callback);
    }
}
