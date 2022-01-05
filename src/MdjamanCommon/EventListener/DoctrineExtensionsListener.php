<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2022 Marcel DJAMAN
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
 * @copyright 2022 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\EventListener;

use Doctrine\Common\EventArgs;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Laminas\Authentication\AuthenticationServiceInterface;

/**
 * Class DoctrineExtensionsListener
 * @package MdjamanCommon\EventListener
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class DoctrineExtensionsListener
{

    /**
     * @var BlameableListener
     */
    protected $blameableListener = null;

    /**
     * @var LoggableListener
     */
    protected $loggableListener = null;

    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;


    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param EventArgs $event
     * @return void
     */
    public function preFlush(EventArgs $event)
    {
        $this->onEvent($event);
    }

    /**
     * @param EventArgs $event
     * @return void
     */
    public function onFlush(EventArgs $event)
    {
        $this->onEvent($event);
    }

    /**
     * @param EventArgs $event
     * @return void
     */
    protected function onEvent(EventArgs $event)
    {
        try {
            if ($this->authenticationService->hasIdentity()) {
                return;
            }

            $identity = $this->authenticationService->getIdentity();

            if (method_exists($event, 'getEntityManager')) {
                $objectManager = $event->getEntityManager();
            } else {
                $objectManager = $event->getDocumentManager();
            }

            $evtManager = $objectManager->getEventManager();
            foreach ($evtManager->getListeners() as $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof BlameableListener) {
                        $this->blameableListener = $listener;
                        continue;
                    }
                    if ($listener instanceof LoggableListener) {
                        $this->loggableListener = $listener;
                        continue;
                    }
                }
            }

            if (null !== $this->blameableListener) {
                $this->blameableListener->setUserValue($identity);
                $evtManager->addEventSubscriber($this->blameableListener);
            }

            if (null !== $this->loggableListener) {
                $this->loggableListener->setUsername($identity);
                $evtManager->addEventSubscriber($this->loggableListener);
            }
        } catch (\Exception $ex) {
        }
    }
}
