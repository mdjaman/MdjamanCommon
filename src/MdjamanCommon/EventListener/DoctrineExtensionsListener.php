<?php
/**
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright Copyright (c) 2013 Institut Pasteur de Cote d'Ivoire
 * @license WebCorpor8
 */

namespace MdjamanCommon\EventListener;

use Doctrine\Common\EventArgs;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Zend\Authentication\AuthenticationServiceInterface;

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
        return $this->onEvent($event);
    }

    /**
     * @param EventArgs $event
     * @return void
     */
    public function onFlush(EventArgs $event)
    {
        return $this->onEvent($event);
    }

    /**
     * @param EventArgs $event
     * @return void
     */
    protected function onEvent(EventArgs $event)
    {
        try {
            if ($this->authenticationService->hasIdentity()) {
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
                    $this->loggableListener->setUsername($identity->getId());
                    $evtManager->addEventSubscriber($this->loggableListener);
                }
            }            
        } catch (\Exception $ex) {
        }
        
        return;
    }
}
