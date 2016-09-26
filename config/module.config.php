<?php

namespace MdjamanCommon;

return array(
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
        'invokables' => array(
            'mdjaman_event_manager'   => 'Zend\EventManager\SharedEventManager',
            'Form\Upload'  => Form\UploadForm::class,
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'translate' => Controller\Plugin\Translate::class,
        ),
    ),
);