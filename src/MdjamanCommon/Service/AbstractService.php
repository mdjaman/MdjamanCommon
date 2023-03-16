<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2023 Marcel DJAMAN
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
 * @copyright 2023 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use MdjamanCommon\EventManager\EventManagerAwareTrait;
use MdjamanCommon\EventManager\TriggerEventTrait;
use MdjamanCommon\Model\ModelInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Log\Logger;
use Laminas\Log\Processor\PsrPlaceholder;
use Laminas\Log\Writer\Stream;
use MdjamanCommon\Repository\LogEntryRepositoryInterface;

abstract class AbstractService implements AbstractServiceInterface
{
    use EventManagerAwareTrait;
    use TriggerEventTrait;

    /**
     * @var ObjectManager
     */
    protected ObjectManager $objectManager;

    /**
     * @var ModelInterface
     */
    protected ModelInterface $entity;

    /**
     * @var HydratorInterface
     */
    protected HydratorInterface $hydrator;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var string[]
     */
    protected array $serializerFormat = ['json', 'xml', 'yml'];

    /**
     * @var string
     */
    protected string $logEntryEntity = 'Gedmo\\Loggable\\Entity\\LogEntry';

    /**
     * @var mixed
     */
    protected $logger;

    /**
     * @var bool
     */
    protected bool $eventTriggered = false;


    /**
     * AbstractService constructor.
     *
     * @param ModelInterface $entity
     * @param ObjectManager $objectManager
     */
    public function __construct(ModelInterface $entity, ObjectManager $objectManager)
    {
        $this->setEntity($entity);
        $this->objectManager = $objectManager;
        $this->enableSoftDeleteableFilter(true);

        $this->serializer = \JMS\Serializer\SerializerBuilder::create()
            ->setPropertyNamingStrategy(
                new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(
                    new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy()
                )
            )
            ->setCacheDir(getcwd() . '/data/JMSSerializer')
            ->build();

        $this->hydrator = new DoctrineObject($this->objectManager);
    }

    /**
     * @return ModelInterface
     */
    public function getEntity(): ModelInterface
    {
        return $this->entity;
    }

    /**
     * @param ModelInterface $entity
     * @return AbstractServiceInterface
     */
    public function setEntity(ModelInterface $entity): AbstractServiceInterface
    {
        $this->entity = $entity;
        return $this;
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return $this|AbstractService
     */
    public function setHydrator(HydratorInterface $hydrator): AbstractServiceInterface
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @param mixed $entity
     * @param HydratorInterface|null $hydrator
     * @return array|mixed[]
     */
    public function toArray($entity, ?HydratorInterface $hydrator = null): array
    {
        if (is_array($entity)) {
            return $entity; // cut down on duplicate code
        } elseif (is_object($entity)) {
            if (! $hydrator) {
                $hydrator = $this->getHydrator();
            }
            return $hydrator->extract($entity);
        }
        throw new Exception\InvalidArgumentException(
            'Entity passed to db mapper should be an array or object.'
        );
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param mixed $serializer
     * @return AbstractServiceInterface
     */
    public function setSerializer($serializer): AbstractServiceInterface
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @param ModelInterface $entity
     * @param string|null $format
     * @param array|string|null $groups
     * @return mixed|string
     * @throws \Exception
     */
    public function serialize($entity, ?string $format = 'json', $groups = null)
    {
        if (! in_array($format, $this->serializerFormat)) {
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
     * @param ModelInterface|null $entity
     * @return ModelInterface|mixed|object|null
     */
    public function hydrate(array $data, ?ModelInterface $entity = null)
    {
        if (is_null($entity)) {
            $entity = $this->createEntity();
        }

        $argv = ['data' => &$data, 'entity' => $entity];
        if ($this->eventTriggered) {
            # Gives the possibility to change $argv in listeners
            $this->triggerEvent(__FUNCTION__.'.pre', $argv);
            extract($argv);
        }

        try {
            $entity = $this->getHydrator()->hydrate($data, $entity);
        } catch (\Exception $ex) {
            $hydrator = $this->objectManager->getHydratorFactory();
            $hydrator->hydrate($entity, $data);
        }

        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.post', $argv);
        }

        return $entity;
    }

    /**
     * @param string|object|null $entityName
     * @return ModelInterface
     */
    public function createEntity($entityName = null): ModelInterface
    {
        if (null === $entityName) {
            $entityName = $this->getEntity();
        } elseif (false === class_exists($entityName)) {
            throw new Exception\InvalidArgumentException(
                "'".$entityName."' class doesn't exist. Can't create class."
            );
        }

        return new $entityName;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        $class = $this->getEntityClassName();
        return $this->objectManager->getRepository($class);
    }

    /**
     * @param string|int $id
     * @param string|null $class
     * @return object
     */
    public function getReference($id, ?string $class = null): object
    {
        if ($class === null) {
            $class = $this->getEntityClassName();
        }
        return $this->objectManager->getReference($class, $id);
    }

    /**
     * @param string|null $class
     * @return ClassMetadata
     */
    public function getEntityClassMetadata(?string $class = null): ClassMetadata
    {
        if ($class === null) {
            $class = $this->getEntityClassName();
        }
        return $this->objectManager->getClassMetadata($class);
    }

    /**
     * @param ModelInterface $entity
     * @return \Gedmo\Loggable\Document\LogEntry[]|mixed
     */
    public function getLogEntries(ModelInterface $entity)
    {
        /** @var LogEntryRepositoryInterface $logEntryRepository */
        $logEntryRepository = $this->objectManager->getRepository($this->logEntryEntity);
        return $logEntryRepository->getLogEntries($entity);
    }

    /**
     * @param string|int $id
     * @return ModelInterface|object|null
     */
    public function find($id)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['id' => &$id];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__.'.pre', $argv);
            extract($argv);
        }

        $entity = $this->getRepository()->find($id);

        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__, ['entity' => $entity]);
            $this->triggerEvent(__FUNCTION__ . '.post', ['id' => $id, 'entity' => $entity]);
        }

        return $entity;
    }

    /**
     * @param array $criteria
     * @return ModelInterface|mixed|object|null
     */
    public function findOneBy(array $criteria = []): ?ModelInterface
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['criteria' => &$criteria];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
            extract($argv);
        }

        $entity = $this->getRepository()->findOneBy($criteria);

        if ($this->eventTriggered) {
            $this->triggerEvent('find', ['entity' => $entity]);
            $this->triggerEvent(__FUNCTION__.'.post', ['criteria' => $criteria, 'entity' => $entity]);
        }

        return $entity;
    }

    /**
     * @param string|array $orderBy
     * @return ModelInterface[]|object[]
     */
    public function findAll($orderBy = null): array
    {
        if (is_string($orderBy)) {
            $orderBy = [$orderBy => 'asc'];
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['orderBy' => &$orderBy];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
            extract($argv);
        }

        $entities = $this->getRepository()->findBy(array(), $orderBy);

        /*foreach ($entities as $entity) {
            $this->triggerEvent('find', ['entity' => $entity]);
        }*/

        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.post', ['orderBy' => $orderBy, 'entities' => $entities]);
        }

        return $entities;
    }

    /**
     * @param array $criteria
     * @param mixed $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array|object[]
     */
    public function findBy(array $criteria, $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        if (is_string($orderBy)) {
            $orderBy = [$orderBy => 'asc'];
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['criteria' => &$criteria, 'orderBy' => &$orderBy, 'limit' => &$limit, 'offset' => &$offset];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
            extract($argv);
        }

        $entities = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);

        if ($this->eventTriggered) {
            /*foreach ($entities as $entity) {
                $this->triggerEvent('find', ['entity' => $entity]);
            }*/
            $this->triggerEvent(__FUNCTION__ . '.post', array_merge($argv, ['entities' => $entities]));
        }

        return $entities;
    }

    /**
     * @param ModelInterface|array $entity
     * @param bool $flush
     * @param string|null $event
     * @return ModelInterface
     */
    public function save($entity, bool $flush = true, string $event = null): ModelInterface
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['entity' => &$entity, 'flush' => &$flush];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
            extract($argv);
        }

        if (is_array($entity)) {
            # Means we only have an array of data here
            $data = $entity;
            $entity = null;
            if (array_key_exists('id', $data) && ! empty($data['id'])) {
                # We have an id here > it's an update !
                $entity = $this->find($data['id']);
                if ($entity) {
                    unset($data['id']);
                }
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

        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.post', array_merge($argv, ['saved' => $entity]));
        }

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param bool $flush
     * @return ModelInterface
     */
    public function delete($entity, ?bool $flush = true): ModelInterface
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['entity' => &$entity, 'flush' => &$flush];
        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
            extract($argv);
        }

        if (is_string($entity)) {
            # Means we only have the id of the entity
            $entity = $this->find($entity);
        } elseif (is_array($entity)) {
            # Means we only have criteria precise enough to get the entity
            $entity = $this->findOneBy($entity);
        }

        $this->objectManager->remove($entity);

        if ($flush === true) {
            $this->objectManager->flush();
        }

        if ($this->eventTriggered) {
            $this->triggerEvent(__FUNCTION__ . '.post', array_merge($argv, ['deleted' => $entity]));
        }

        return $entity;
    }

    /**
     * @param bool|null $enable
     * @return AbstractServiceInterface
     */
    public function enableSoftDeleteableFilter(?bool $enable = true): AbstractServiceInterface
    {
        if (method_exists($this->objectManager, 'getFilterCollection')) {
            $filters = $this->objectManager->getFilterCollection();
        } else {
            $filters = $this->objectManager->getFilters();
        }

        if ($enable) {
            $filters->enable('softDeleteable');
        } else {
            $filters->disable('softDeleteable');
        }
        return $this;
    }

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array|mixed
     * @throws \Exception
     */
    public function filters(array $filters, ?array $orderBy = [], ?int $limit = null, ?int $offset = null)
    {
        $searchParam = $filters['q'] ?? '';
        unset($filters['q']);
        if ($searchParam === '') {
            return $this->getMatchingRecords($filters, $orderBy, $limit, $offset);
        }

        if (! method_exists($this->getEntity(), 'getClassFields')) {
            return $this->getMatchingRecords($filters, $orderBy, $limit, $offset);
        }

        if (! method_exists($this->getRepository(), 'fullSearchText')) {
            return $this->getMatchingRecords($filters, $orderBy, $limit, $offset);
        }

        return $this->search($searchParam, $filters, $orderBy, $limit, $offset);
    }

    /**
     * @param string $searchTerm
     * @param array|null $filters
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed
     * @throws \ReflectionException
     */
    public function search(
        string $searchTerm,
        ?array $filters = [],
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        $entity = $this->getEntity();
        $fields = $entity->getClassFields();
        $criteria = [];
        foreach ($fields as $field) {
            $criteria[$field] = $searchTerm;
        }

        $sort = null;
        $dir = 1;
        if (is_array($orderBy) && count($orderBy)) {
            foreach ($orderBy as $k => $v) {
                $sort = $k;
                $dir = $v;
            }
        }

        return $this->getRepository()->fullSearchText(
            $criteria,
            $sort,
            $dir,
            $limit,
            $offset,
            $filters
        );
    }

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return object[]
     * @throws \Exception
     */
    protected function getMatchingRecords(
        array $filters,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
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
     * @param array|null $filters
     * @return int
     * @throws \Exception
     */
    public function countMatchingRecords(?array $filters = []): int
    {
        $matches = $this->filters($filters);
        return count($matches);
    }

    /**
     * @param array $filters
     * @return Criteria
     * @throws \Exception
     */
    protected function buildCriteria(array $filters): Criteria
    {
        $entity = $this->hydrate($filters);

        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $methodPrefixes = ['get', 'is'];
        foreach ($filters as $key => $value) {
            $exists = false;
            $i = 0;
            $getter = null;
            while ($i < count($methodPrefixes)) {
                $getter = $methodPrefixes[$i] . ucfirst($key);
                if (method_exists($entity, $getter)) {
                    $exists = true;
                    break;
                }
                $i++;
            }

            if ($exists) {
                $criteria->andWhere($expr->eq($key, $entity->{$getter}()));
            }
        }

        return $criteria;
    }

    /**
     * @return string
     */
    private function getEntityClassName()
    {
        return is_string($this->getEntity()) ? $this->getEntity() : get_class($this->getEntity());
    }

    /**
     * @param mixed $logger
     * @return AbstractServiceInterface
     */
    public function setLogger($logger = null): AbstractServiceInterface
    {
        if (! $logger) {
            $writer = new Stream([
                'stream' => getcwd() . '/data/log/application.log',
            ]);
            $logger = new Logger();
            $logger->addWriter($writer);
            $logger->addProcessor(new PsrPlaceholder);
        }
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return object
     */
    public function getLogger()
    {
        if (! $this->logger) {
            $this->setLogger();
        }
        return $this->logger;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @return string
     */
    public function getLogEntryEntity(): string
    {
        return $this->logEntryEntity;
    }

    /**
     * @param string $logEntryEntity
     * @return $this
     */
    public function setLogEntryEntity(string $logEntryEntity): AbstractServiceInterface
    {
        $this->logEntryEntity = $logEntryEntity;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEventTriggered(): bool
    {
        return $this->eventTriggered;
    }

    /**
     * @param bool $eventTriggered
     */
    public function setEventTriggered(bool $eventTriggered): void
    {
        $this->eventTriggered = $eventTriggered;
    }
}
