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

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use JMS\Serializer\SerializerInterface;
use Laminas\Hydrator\HydratorInterface;
use MdjamanCommon\Model\ModelInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface AbstractServiceInterface
 *
 * @package MdjamanCommon\Service
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
interface AbstractServiceInterface
{
    /**
     * @return string|object
     */
    public function getEntity();

    /**
     * @param string|object $entity
     * @return $this
     */
    public function setEntity($entity): AbstractService;

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface;

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setHydrator(HydratorInterface $hydrator): AbstractService;

    /**
     * Uses the hydrator to convert the entity to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $entity
     * @param HydratorInterface|null $hydrator
     * @return array
     */
    public function toArray(object $entity, HydratorInterface $hydrator = null): array;

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface;

    /**
     * @param mixed $serializer
     * @return $this
     */
    public function setSerializer($serializer = null): AbstractServiceInterface;

    /**
     * @param array|ModelInterface $entity
     * @param string $format
     * @param string|null $groups
     * @return mixed|string
     * @throws \Exception
     */
    public function serialize($entity, string $format = 'json', string $groups = null);

    /**
     * @param array $data
     * @param ModelInterface|null $entity
     * @return ModelInterface|mixed
     * @throws \Exception
     */
    public function hydrate(array $data, ModelInterface $entity = null);

    /**
     * Creates a new instance of the given entityName or of the already known
     * one whose FQDN is stored in the className property.
     *
     * @param string|null $entityName
     * @return ModelInterface|mixed
     * @throws \Exception
     */
    public function createEntity(string $entityName = null);

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * Get Entity Reference
     *
     * @param string|int $id
     * @param string|null $class
     * @return object
     */
    public function getReference($id, string $class = null): object;

    /**
     * @param string|null $class
     * @return ClassMetadata
     */
    public function getEntityClassMetadata(string $class = null): ClassMetadata;

    /**
     * Return log entries
     * From Loggable behavioral extension for Gedmo
     *
     * @param ModelInterface $entity
     * @return mixed
     */
    public function getLogEntries(ModelInterface $entity);

    /**
     * @param string|int $id
     * @return ModelInterface|mixed
     *
     * @triggers find.pre
     * @triggers find.post
     * @triggers find
     */
    public function find($id);

    /**
     * @param array $criteria
     * @return ModelInterface|mixed
     *
     * @triggers findOneBy.pre
     * @triggers findOneBy.post
     * @triggers find
     */
    public function findOneBy(array $criteria);

    /**
     * @param array|string $orderBy
     * @return array
     *
     * @triggers findAll.pre
     * @triggers findAll.post
     * @triggers find
     */
    public function findAll($orderBy = null): array;

    /**
     * @param array $criteria
     * @param array|string|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     *
     * @triggers findBy.pre
     * @triggers findBy.post
     * @triggers find
     */
    public function findBy(array $criteria, $orderBy = null, $limit = null, $offset = null): array;

    /**
     * @param array|ModelInterface $entity
     * @param bool $flush
     * @param null|string $event
     * @return array|ModelInterface|mixed|null
     * @throws \Exception
     */
    public function save($entity, bool $flush = true, string $event = null);

    /**
     * @param string|array|ModelInterface $entity
     * @param bool $flush
     * @return ModelInterface
     */
    public function delete($entity, bool $flush = true): ModelInterface;

    /**
     * enable/disable entityManager softDeleteable
     *
     * @param bool $enable
     * @return $this
     */
    public function enableSoftDeleteableFilter(bool $enable = true): AbstractService;

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array|mixed
     * @throws \Exception
     */
    public function filters(
        array $filters,
        array $orderBy = null,
        int $limit = null,
        int $offset = null
    );

    /**
     * @param string $searchTerm
     * @param array $filters
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return mixed
     */
    public function search(
        string $searchTerm,
        array $filters = [],
        array $orderBy = null,
               $limit = null,
               $offset = null
    );

    /**
     * @param array $filters
     * @return int
     * @throws \Exception
     */
    public function countMatchingRecords(array $filters): int;

    /**
     * @param mixed $logger
     * @return $this
     */
    public function setLogger($logger = null): AbstractService;

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;

    /**
     * @return ObjectManager
     */
    public function getObjectManager(): ObjectManager;
}
