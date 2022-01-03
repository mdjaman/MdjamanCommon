<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Marcel Djaman
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
 * @copyright 2020 Marcel Djaman
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Service;

use Elastica\Client;
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
    protected $index = 'app_idx';

    /**
     * @var array
     */
    protected $types = [];

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
     * @param string $type
     * @param null $limit
     * @param int $offset
     * @return mixed
     */
    public function search($query, $type = 'patient', $limit = null, $offset = 0)
    {
        if (empty($query)) {
            return false;
        }

        $index = $this->index;

        $query = (string) $query;

        // Define a Query. We want a string query.
        $esQueryString = new \Elastica\Query\QueryString($query);
        $esQuery = new \Elastica\Query($esQueryString);

        if ($limit) {
            $esQuery->setSize($limit)
                    ->setFrom($offset);
        }

        // Load index
        $esIdx = $this->getClient()->getIndex($index);
        $type = $esIdx->getType($type);

        /* if (!in_array($type, $this->types) || $type == 'all') {
            foreach ($this->types as $t) {
                $esIdx->addType($t);
            }
        } */

        $esIdx->refresh();
        //Search on the index.
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
     * @param $user
     * @param integer|null $limit
     * @param integer $offset
     * @return array
     */
    public function getUserSearch($user, $limit = null, $offset = 0)
    {
        $r_key = 'usr_src:' . $user->getId();

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
    public function setIndex($index)
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
     * @param array $parameters the connection parameters
     * @param array $options the profile options
     * @return $this
     */
    public function setPredis($parameters = null, $options = null)
    {
        $predisOptions = $this->getServiceManager()->get('ipci_predis_options');

        if (null == $parameters) {
            $parameters = $predisOptions->getDefaultParameter();
        }

        if (null == $options) {
            $options = $predisOptions->getDefaultSettings();
        }

        $this->predis = new \Predis\Client($parameters, $options);
        return $this;
    }
}
