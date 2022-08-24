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
 * Interface SearchServiceInterface
 *
 * @package MdjamanCommon\Service
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
interface SearchServiceInterface
{
    /**
     * Performs a search
     *
     * @param string $query
     * @param string $type
     * @param null $limit
     * @param int $offset
     * @return mixed
     */
    public function search($query, $type = 'patient', $limit = null, $offset = 0);

    /**
     * Save a user search to redis db
     *
     * @param string $query
     * @param $user
     */
    public function saveSearch($query, $user);

    /**
     * Get a user saved searches
     *
     * @param $user
     * @param integer|null $limit
     * @param integer $offset
     * @return array
     */
    public function getUserSearch($user, $limit = null, $offset = 0);

    /**
     * @return mixed
     */
    public function getClient();

    /**
     * @param array|null $config
     * @return $this
     */
    public function setClient(array $config = null);

    /**
     * @param string $index
     * @return $this
     */
    public function setIndex($index);

    /**
     * Get predis client
     */
    public function getPredis();

    /**
     * Set predis client
     *
     * @param array $parameters the connection parameters
     * @param array $options the profile options
     * @return $this
     */
    public function setPredis($parameters = null, $options = null);
}
