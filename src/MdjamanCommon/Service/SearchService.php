<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2022 Marcel DJAMAN
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright 2022 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Service;

use Elastica\Client;
use Elastica\ResultSet;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;
use Laminas\ServiceManager\ServiceManager;

/**
 * Class SearchService
 * @package MdjamanCommon\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class SearchService implements SearchServiceInterface
{
    use ServiceManagerAwareTrait;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Predis\Client
     */
    protected $predis;

    /**
     * @var string
     */
    protected string $index = 'app_idx';


    /**
     * SearchService constructor.
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->setServiceManager($serviceManager);
        $this->setClient();
    }

    /**
     * Performs a search
     *
     * @param string $query
     * @param int|null $limit
     * @param int $offset
     * @return ResultSet
     */
    public function search(string $query, int $limit = null, int $offset = 0): ResultSet
    {

        // Define a Query. We want a string query.
        $esQueryString = new \Elastica\Query\QueryString($query);
        $esQuery = new \Elastica\Query($esQueryString);

        if ($limit) {
            $esQuery->setSize($limit)
                    ->setFrom($offset);
        }

        // Load index
        $esIdx = $this->getClient()->getIndex($this->index);

        $esIdx->refresh();
        return $esIdx->search($esQuery);
    }

    /**
     * Save a user search to redis db
     *
     * @param array|string $query
     * @param $user
     */
    public function saveSearch($query, $user)
    {
        $r_key = 'usr_src:' . $user->getId();

        $redis = $this->getPredis();
        $redis->lpush($r_key, (array) $query);
    }

    /**
     * Get a user saved searches
     *
     * @param string $user
     * @param integer|null $limit
     * @param integer $offset
     * @return array
     */
    public function getUserSearch(string $user, int $limit = null, $offset = 0): array
    {
        $r_key = 'usr_src:' . $user;

        $redis = $this->getPredis();

        if ($limit == null) {
            $limit = $redis->llen($r_key);
        }

        return $redis->lrange($r_key, $offset, $limit);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->setClient();
        }

        return $this->client;
    }

    /**
     * @param array|null $config
     * @return $this
     */
    public function setClient(array $config = null)
    {
        if (!$config) {
            $config = ['url' => 'http://localhost:9200/'];
        }
        $this->client = new Client($config);
        return $this;
    }

    /**
     * @param string $index
     * @return $this
     */
    public function setIndex(string $index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Get predis client
     */
    public function getPredis()
    {
        if (!$this->predis) {
            $this->setPredis();
        }
        return $this->predis;
    }

    /**
     * Set predis client
     *
     * @param array|null $parameters the connection parameters
     * @param array|null $options the profile options
     * @return $this
     */
    public function setPredis(array $parameters = null, array $options = null): SearchService
    {
        $this->predis = new \Predis\Client($parameters, $options);
        return $this;
    }
}
