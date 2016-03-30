<?php
/**
 *
 * The MIT License (MIT)

 * Copyright (c) 2015 Marcel Djaman

 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.

 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright 2015 Marcel Djaman
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Service;

use Identity\Entity\User;
use Zend\ServiceManager\ServiceManager;
use Application\Provider\ServiceManagerAwareTrait;

class SearchService
{
    use ServiceManagerAwareTrait;

    protected $client;

    protected $predis;

    protected $index = 'ipciidx';

    protected $types = [
        'analyse',
        'ets',
        'examen',
        'patient',
        'personnel',
        'prescripteur',
        'visite',
    ];

    public function __construct(ServiceManager $serviceManager)
    {
        $this->setServiceManager($serviceManager);
        $this->setClient();
    }

    public function search($query, $type = 'patient', $limit = null, $offset = 0)
    {
        if (empty($query)) {
            return false;
        }

        $index = $this->index;

        $query = (string) $query;
        $search = new \Elastica\Search($this->getClient());

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
        $esResultSet = $esIdx->search($esQuery);
        return $esResultSet;
    }

    /**
     * Save a user search to redis db
     * @param string $query
     * @param User $user
     */
    public function saveSearch($query, User $user)
    {
    	$r_key = 'usr_src:' . $user->getId();

    	$redis = $this->getPredis();
    	$redis->lpush($r_key, $query);
    }

    /**
     * Get a user saved searches
     * @param User $user
     * @param string $limit
     * @param integer $offset
     * @return array
     */
    public function getUserSearch(User $user, $limit = null, $offset = 0)
    {
    	$r_key = 'usr_src:' . $user->getId();

    	$redis = $this->getPredis();

    	if ($limit == null) {
            $limit = $redis->llen($r_key);
        }

        $search = $redis->lrange($r_key, $offset, $limit);

    	return $search;
    }

    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->setClient();
        }

        return $this->client;
    }

    public function setClient(array $config = null)
    {
        if (!$config) {
            $config = ['url' => 'http://localhost:9200/'];
        }
        $this->client = new \Elastica\Client($config);
    }

    public function setIndex($index)
    {
        $this->index = $index;
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
    }
}
