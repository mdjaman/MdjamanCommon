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

namespace MdjamanCommon\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 * @package MdjamanCommon\Options
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class ModuleOptions extends AbstractOptions implements ModuleOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    
    /**
     * @var string
     */
    protected $logEntryEntityClass = 'MdjamanCommon\Entity\LogEntry';

    /**
     * @var string
     */
    protected $userEntityClass = 'Identity\Entity\User';

    /**
     * @return string
     */
    public function getLogEntryEntityClass()
    {
        return $this->logEntryEntityClass;
    }

    /**
     * @param string $logEntryEntityClass
     * @return ModuleOptions
     */
    public function setLogEntryEntityClass($logEntryEntityClass)
    {
        $this->logEntryEntityClass = $logEntryEntityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserEntityClass()
    {
        return $this->userEntityClass;
    }

    /**
     * @param string $userEntityClass
     * @return ModuleOptions
     */
    public function setUserEntityClass($userEntityClass)
    {
        $this->userEntityClass = $userEntityClass;
        return $this;
    }
}
