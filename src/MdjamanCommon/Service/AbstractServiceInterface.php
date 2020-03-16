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

use Doctrine\Common\Persistence\ObjectManager;
use MdjamanCommon\Model\ModelInterface;
use Psr\Log\LoggerInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Interface AbstractServiceInterface
 * @package MdjamanCommon\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
interface AbstractServiceInterface
{
    /**
     * @return mixed
     */
    public function getEntity();

    /**
     * @param string $entity
     * @return $this
     */
    public function setEntity($entity);

    /**
     * @return HydratorInterface
     */
    public function getHydrator();

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setHydrator(HydratorInterface $hydrator);

    /**
     * Uses the hydrator to convert the entity to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $entity
     * @return array
     */
    public function toArray($entity, HydratorInterface $hydrator = null);

    /**
     * @return mixed
     */
    public function getSerializer();

    /**
     * @param mixed $serializer
     * @return $this
     */
    public function setSerializer($serializer = null);

    /**
     * @param array|ModelInterface $entity
     * @param string $format
     * @param array|null $groups
     * @return string
     */
    public function serialize($entity, $format = 'json', $groups = null);

    /**
     * @param array $data
     * @param ModelInterface $entity
     * @return ModelInterface
     */
    public function hydrate($data, $entity = null);

    /**
     * Creates a new instance of the given entityName or of the already known
     * one whose FQDN is stored in the className property.
     *
     * @param string $entityName
     * @return ModelInterface
     * @throws \Exception
     */
    public function createEntity($entityName = null);

    public function getRepository();

    /**
     * Get Entity Reference
     *
     * @param string|int $id
     * @param string|null $class
     * @return mixed
     */
    public function getReference($id, $class = null);

    /**
     * @param null $class
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getEntityClassMetadata($class = null);

    /**
     * Return log entries
     * From Loggable behavioral extension for Gedmo
     *
     * @param ModelInterface $entity
     * @return void
     */
    public function getLogEntries(ModelInterface $entity);

    /**
     * @param string $id
     * @return ModelInterface
     *
     * @triggers find.pre
     * @triggers find.post
     * @triggers find
     */
    public function find($id);

    /**
     * @param array $criteria
     * @return ModelInterface
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
    public function findAll($orderBy = null);

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
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param array|ModelInterface $entity
     * @param bool $flush
     * @param string $event Overrides the default event name
     * @return ModelInterface
     */
    public function save($entity, $flush = true, $event = null);

    /**
     * @param string|array|ModelInterface $entity
     * @param bool $flush
     * @return ModelInterface
     */
    public function delete($entity, $flush = true);

    /**
     * enable/disable entityManager softDeleteable
     * @param boolean $enable
     * @return void
     */
    public function enableSoftDeleteableFilter($enable = true);

    /**
     * @param array $filters
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function filters(array $filters, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param $filters
     * @return int
     */
    public function countMatchingRecords($filters);

    /**
     * @param mixed $logger
     * @return $this
     */
    public function setLogger($logger = null);

    /**
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * @return ObjectManager
     */
    public function getObjectManager();
}
