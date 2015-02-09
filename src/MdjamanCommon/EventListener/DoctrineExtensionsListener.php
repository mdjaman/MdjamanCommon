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
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineExtensionsListener
{
    /**
     * @var ServiceLocator
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


    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->sl = $serviceLocator;
    }

    public function preFlush(EventArgs $event)
    {
        $authenticationService = $this->sl->get('zfcuser_auth_service');
        if (!$authenticationService instanceof \Zend\Authentication\AuthenticationService) {
            return;
        }

        if ($authenticationService->hasIdentity()) {
            $identity = $authenticationService->getIdentity();
            $entityManager = $event->getEntityManager();
            $evtManager = $entityManager->getEventManager();

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
    }

}
