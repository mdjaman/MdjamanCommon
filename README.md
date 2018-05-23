MdjamanCommon
=======================

Introduction
------------
Module that provides common functionality and library code for ZF2/ZF3 applications integrated with doctrine ORM or ODM.

Installation
------------

Using Composer (recommended and only way)
----------------------------
Mdjaman\MdjamanCommon is available through composer. Add "mdjaman/mdjaman-common" to your composer.json list. 

Choose most recent v2 tag for zf2

    "mdjaman/mdjaman-common": "2.*"
    
Choose most recent v3 tag for zf3

    "mdjaman/mdjaman-common": "3.*"
    
Enable the module in your config/application.config.php file. Add an entry ```MdjamanCommon``` to the list of enabled 
modules.

Usage
------------
To make this MdjamanCommon deal with Gedmo\Blameable extension for you, make sure your AuthenticationService implements 
Zend\Authenticate\AuthenticationServiceInterface and add this alias **mdjaman_auth_service** in a ```aliases``` 
section under ```service_manager``` in your **module.config.php**

    'service_manager' => [
            'aliases' => [
                'mdjaman_auth_service' => <MyGreatAuthenticationService>,
            ],
            ...
        ]
    ],
    
More to come!
