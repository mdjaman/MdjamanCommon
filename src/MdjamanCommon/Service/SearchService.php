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

use Elastica\Client;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;
use Zend\ServiceManager\ServiceManager;

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
