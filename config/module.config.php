<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
        'aliases' => array(
        ),
        'invokables' => array(
            'ipci_event_manager'   => 'Zend\EventManager\SharedEventManager',
            'NotificationListener' => 'Application\Listener\NotificationListener',
            'ExamenListener'       => 'Application\Listener\ExamenListener',
            'PrescripteurToEtsListener' => 'Application\Listener\PrescripteurToEtsListener',
            'VisiteStrategy'       => 'Application\View\VisiteStrategy',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'translate' => 'MdjamanCommon\Controller\Plugin\Translate',
        ),
        'factories' => array(
        )
    ),
);