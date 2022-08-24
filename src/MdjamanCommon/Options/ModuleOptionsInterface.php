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

/**
 * Interface ModuleOptionsInterface
 * @package MdjamanCommon\Options
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
interface ModuleOptionsInterface
{
    /**
     * @return string
     */
    public function getLogEntryEntityClass();

    /**
     * @param string $logEntryEntityClass
     * @return ModuleOptions
     */
    public function setLogEntryEntityClass($logEntryEntityClass);

    /**
     * @return string
     */
    public function getUserEntityClass();

    /**
     * @param string $userEntityClass
     * @return ModuleOptions
     */
    public function setUserEntityClass($userEntityClass);
}
