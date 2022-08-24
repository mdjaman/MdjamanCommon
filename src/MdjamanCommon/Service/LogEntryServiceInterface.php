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

namespace MdjamanCommon\Service;

/**
 * Interface LogEntryServiceInterface
 * @package MdjamanCommon\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
interface LogEntryServiceInterface extends AbstractServiceInterface
{
    /**
     * @param $resultset
     * @return array
     */
    public function resultWrapper($resultset);

    /**
     * Filter
     *
     * @param array $filters
     * @return mixed
     */
    public function filter(array $filters = null);
}
