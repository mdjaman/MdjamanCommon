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

use Doctrine\Common\Collections\Criteria;
use MdjamanCommon\Entity\BaseEntity;
use MdjamanCommon\EventManager\EventManagerAwareTrait;
use MdjamanCommon\EventManager\TriggerEventTrait;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;
use JMS\Serializer\SerializationContext;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Log\LoggerInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use MdjamanCommon\Model\ModelInterface;

/**
 * Class AbstractService
 * @package MdjamanCommon\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
abstract class AbstractService implements AbstractServiceInterface
{
    use ServiceManagerAwareTrait;
    use EventManagerAwareTrait;
    use TriggerEventTrait;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $entity;
    
    /**
     * @var DoctrineObject 
     */
    protected $hydrator;
    
    /**
     * @var \JMS\Serializer\Serializer 
     */
    protected $serializer;
    
    /**
     * @var array 
     */
    protected $serializerFormat = array('json', 'xml', 'yml');
    
    /**
     * @var string 
     */
    protected $logEntryEntity = 'Gedmo\\Loggable\\Entity\\LogEntry';
    
    protected $logger;

    /**
     * AbstractService constructor.
     * @param null $entityName
     * @param ObjectManager $objectManager
     */
    public function __construct($entityName = null, ObjectManager $objectManager)
    {
        if (!is_null($entityName)) {
            $this->setEntity($entityName);
        }

        $this->objectManager = $objectManager;
        $this->enableSoftDeleteableFilter(true);
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new DoctrineObject($this->objectManager, get_class($this->entity));
        }
        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Uses the hydrator to convert the entity to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $entity
     * @return array
     */
    public function toArray($entity, HydratorInterface $hydrator = null)
    {
        if (is_array($entity)) {
            return $entity; // cut down on duplicate code
        } elseif (is_object($entity)) {
            if (!$hydrator) {
                $hydrator = $this->getHydrator();
            }
            return $hydrator->extract($entity);
        }
        throw new Exception\InvalidArgumentException('Entity passed to db mapper should be an array or object.');
    }

    public function getSerializer()
    {
        if (!$this->serializer) {
            $this->setSerializer();
        }

        return $this->serializer;
    }

    public function setSerializer($serializer = null)
    {
        if (!$serializer) {
            $serializer = $this->getServiceManager()->get('jms_serializer.serializer');
        }
        
        $this->serializer = $serializer;
    }

    /**
     * @param array|\MdjamanCommon\Entity\BaseEntity $entity
     * @param string $format
     * @param array|null $groups
     * @return string
     */
    public function serialize($entity, $format = 'json', $groups = null)
    {
        if (!in_array($format, $this->serializerFormat)) {
            throw new Exception\InvalidArgumentException('Format ' . $format . ' is not valid');
        }
        $serializer = $this->getSerializer();
        
        $context = SerializationContext::create()->enableMaxDepthChecks();
        $groups = (array) $groups;
        if (count($groups)) {
            $context->setGroups($groups);
        }
        $serialize  = $serializer->serialize($entity, $format, $context);

        if ($format === 'json') {
            $serialize = json_decode($serialize);
        }
        return $serialize;
    }

    /**
     * @param array $data
     * @param \MdjamanCommon\Entity\BaseEntity $entity
     * @return \MdjamanCommon\Entity\BaseEntity
     */
    public function hydrate($data, $entity = null)
    {
        if (is_null($entity)) {
            $entity = $this->createEntity();
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['data' => &$data, 'entity' => $entity];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        $hydrator = new DoctrineObject($this->objectManager);
        $hydrator->hydrate($data, $entity);

        $this->triggerEvent(__FUNCTION__.'.post', $argv);

        return $entity;
    }

    /**
     * Creates a new instance of the given entityName or of the already known
     * one whose FQDN is stored in the className property.
     *
     * @param string $entityName
     * @return \MdjamanCommon\Entity\BaseEntity
     * @throws \Exception
     */
    public function createEntity($entityName = null)
    {
        if (null === $entityName) {
            $entityName = $this->getEntity();
            if ( !$entityName) {
                // @todo throw good Exception
                throw new Exception\InvalidArgumentException("entityName not set. Can't create class.");
            }
        } else {
            if (false === class_exists($entityName)) {
                // @todo throw good Exception
                throw new Exception\InvalidArgumentException("'".$entityName."' class doesn't exist. Can't create class.");
            }
        }

        return new $entityName;
    }

    public function getRepository()
    {
        if (is_object($this->getEntity())) {
            $class = get_class($this->getEntity());
        } else {
            $class = $this->getEntity();
        }

        return $this->objectManager->getRepository($class);
    }

    /**
     * Get Entity Reference
     * 
     * @param string|int $id
     * @param string|null $class
     * @return mixed
     */
    public function getReference($id, $class = null)
    {
        if (null === $class) {
            $class = get_class($this->getEntity());
        }
        return $this->objectManager->getReference($class, $id);
    }

    /**
     * Return log entries
     * From Loggable behavioral extension for Gedmo
     *
     * @param ModelInterface $entity
     * @return void
     */
    public function getLogEntries(ModelInterface $entity)
    {
        $logEntryEntity = $this->objectManager->getRepository($this->logEntryEntity);
        return $logEntryEntity->getLogEntries($entity);
    }

    /**
     * @param string $id
     * @return BaseEntity
     *
     * @triggers find.pre
     * @triggers find.post
     * @triggers find
     */
    public function find($id)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['id' => &$id];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        $entity = $this->getRepository()->find($id);

        $this->triggerEvent(__FUNCTION__, ['entity' => $entity]);
        $this->triggerEvent(__FUNCTION__.'.post', ['id' => $id, 'entity' => $entity]);

        return $entity;
    }

    /**
     * @param array $criteria
     * @return BaseEntity
     *
     * @triggers findOneBy.pre
     * @triggers findOneBy.post
     * @triggers find
     */
    public function findOneBy(array $criteria)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['criteria' => &$criteria];
        $this->triggerEvent(__FUNCTION__ .'.pre', $argv);
        extract($argv);

        $entity = $this->getRepository()->findOneBy($criteria);

        $this->triggerEvent('find', ['entity' => $entity]);
        $this->triggerEvent(__FUNCTION__.'.post', ['criteria' => $criteria, 'entity' => $entity]);

        return $entity;
    }

    /**
     * @param array|string $orderBy
     * @return array
     *
     * @triggers findAll.pre
     * @triggers findAll.post
     * @triggers find
     */
    public function findAll($orderBy = null)
    {
        if (is_string($orderBy)) {
            $orderBy = [$orderBy => 'asc'];
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['orderBy' => &$orderBy];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        $entities = $this->getRepository()->findBy(array(), $orderBy);

        /*foreach ($entities as $entity) {
            $this->triggerEvent('find', ['entity' => $entity]);
        }*/

        $this->triggerEvent(__FUNCTION__.'.post', ['orderBy' => $orderBy, 'entities' => $entities]);

        return $entities;
    }

    /**
     * @param array $criteria
     * @param array|string $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     *
     * @triggers findBy.pre
     * @triggers findBy.post
     * @triggers find
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (is_string($orderBy)) {
            $orderBy = [$orderBy => 'asc'];
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['criteria' => &$criteria, 'orderBy' => &$orderBy, 'limit' => &$limit, 'offset' => &$offset];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        $entities = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);

        /*foreach ($entities as $entity) {
            $this->triggerEvent('find', ['entity' => $entity]);
        }*/

        $this->triggerEvent(__FUNCTION__ . '.post', array_merge($argv, ['entities' => $entities]));

        return $entities;
    }

    /**
     * @param array|BaseEntity $entity
     * @param bool $flush
     * @param string $event Overrides the default event name
     * @return BaseEntity
     */
    public function save($entity, $flush = true, $event = null)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['entity' => &$entity, 'flush' => &$flush];
        $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
        extract($argv);

        if (is_array($entity)) {
            # Means we only have an array of data here
            $data = $entity;
            $entity = null;
            if (array_key_exists('id', $data) && !empty($data['id'])) {
                # We have an id here > it's an update !
                $entity = $this->find($data['id']);
                unset($data['id']);
            }
            $entity = $this->hydrate($data, $entity);
        }

        $this->objectManager->persist($entity);

        if ($flush === true) {
            $this->objectManager->flush();
        }

        if (null !== $event && $event !== __FUNCTION__) {
            $this->triggerEvent($event, array_merge($argv, ['saved' => $entity]));
        }
        $this->triggerEvent(__FUNCTION__.'.post', array_merge($argv, ['saved' => $entity]));

        return $entity;
    }

    /**
     * @param string|array|BaseEntity $entity
     * @param bool $flush
     * @return BaseEntity
     */
    public function delete($entity, $flush = true)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['entity' => &$entity, 'flush' => &$flush];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        if (is_string($entity)) {
            # Means we only have the id of the entity
            $entity = $this->find($entity);
        } else if (is_array($entity)) {
            # Means we only have criteria precise enough to get the entity
            $entity = $this->findOneBy($entity);
        }

        $this->objectManager->remove($entity);

        if ($flush === true) {
            $this->objectManager->flush();
        }

        $this->triggerEvent(__FUNCTION__.'.post', array_merge($argv, ['deleted' => $entity]));

        return $entity;
    }
    
    /**
     * enable/disable entityManager softDeleteable
     * @param boolean $enable
     * @return void
     */
    public function enableSoftDeleteableFilter($enable = true)
    {
        if (method_exists($this->objectManager, 'getFilterCollection')) {
            $filters = $this->objectManager->getFilterCollection();
        } else {
            $filters = $this->objectManager->getFilters();
        }
        
        if (true === $enable) {
            $filters->enable('softDeleteable');
        } else {
            $filters->disable('softDeleteable');
        }
    }

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function filters(array $filters, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getMatchingRecords($filters, $orderBy, $limit, $offset);
    }

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param null|int $limit
     * @param null|int $offset
     * @return mixed
     */
    protected function getMatchingRecords(array $filters, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria = $this->buildCriteria($filters);

        if (null !== $orderBy && count($orderBy)) {
            $criteria->orderBy($orderBy);
        }

        if (null !== $limit) {
            $criteria->setMaxResults($limit);
        }

        if (null !== $offset) {
            $criteria->setFirstResult($offset);
        }

        return $this->getRepository()->matching($criteria);
    }

    /**
     * @param $filters
     * @return int
     */
    public function countMatchingRecords($filters)
    {
        $matchings = $this->filters($filters, []);
        return count($matchings);
    }

    /**
     * @param array $filters
     * @return Criteria
     */
    protected function buildCriteria(array $filters)
    {
        $entity = $this->hydrate($filters);

        $expr = Criteria::expr();
        $criteria = Criteria::create();

        foreach ($filters as $key => $value) {
            $method = 'get' . ucfirst($key);
            $criteria->andWhere($expr->eq($key, $entity->{$method}()));
        }

        return $criteria;
    }

    /**
     * @param string $logger
     * @param bool $isService
     */
    public function setLogger($logger = 'Zend\\Log\\Logger', $isService = true)
    {
        if ($isService == true) {
            if (!$this->getServiceManager()->has($logger)) {
                throw new Exception\InvalidArgumentException('Logger service not found!');
            }
            $logger = $this->getServiceManager()->get($logger);
        }
        
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->setLogger();
        }
        return $this->logger;
    }
    
    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

}
