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
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DoctrineExtensionsListener
 * @package MdjamanCommon\EventListener
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class DoctrineExtensionsListener
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $sl;

    /**
     * @var BlameableListener
     */
    protected $blameableListener = null;

    /**
     * @var LoggableListener
     */
    protected $loggableListener = null;

    protected $authServiceName;
    
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $authSceName
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, $authSceName = 'zfcuser_auth_service')
    {
        $this->sl = $serviceLocator;
        $this->authServiceName = $authSceName;
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
            if (is_string($this->authServiceName)) {
                if (!$this->sl->has($this->authServiceName)) {
                    throw new \Exception(sprintf('Couldn\'t find Service %s', $this->authServiceName));
                }
                $authenticationService = $this->sl->get($this->authServiceName);
            } else {
                $authenticationService = $this->authServiceName;
            }

            if (!$authenticationService instanceof AuthenticationService) {
                return;
            }

            if ($authenticationService->hasIdentity()) {
                $identity = $authenticationService->getIdentity();

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

                if (null != $this->blameableListener) {
                    $this->blameableListener->setUserValue($identity);
                    $evtManager->addEventSubscriber($this->blameableListener);
                }

                if (null != $this->loggableListener) {
                    $this->loggableListener->setUsername($identity->getId());
                    $evtManager->addEventSubscriber($this->loggableListener);
                }
            }            
        } catch (\Exception $ex) {
            return;
        }

    }

}
