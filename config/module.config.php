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

namespace MdjamanCommon;

use MdjamanCommon\Options\ModuleOptions;
use MdjamanCommon\Service\LogEntryService;
use Zend\EventManager\SharedEventManager;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'aliases' => [
            'Form\Upload' => Form\UploadForm::class,
        ],
        'factories' => [
            ModuleOptions::class => Factory\Options\ModuleOptionsFactory::class,
            LogEntryService::class => Factory\Service\LogEntryServiceFactory::class,
            Form\UploadForm::class => InvokableFactory::class,
        ],
        'invokables' => [
            'mdjaman_event_manager' => SharedEventManager::class,
        ],
    ],
];
