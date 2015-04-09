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

use MdjamanCommon\Document\BaseDocument;
use MdjamanCommon\EventManager\EventManagerAwareTrait;
use MdjamanCommon\EventManager\TriggerEventTrait;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Stdlib\Hydrator\HydratorInterface;
use MdjamanCommon\Model\ModelInterface;

/**
 * Abstract Service for doctrine odm
 */
abstract class AbstractDocumentService
{
    
    use ServiceManagerAwareTrait;
    use EventManagerAwareTrait;
    use TriggerEventTrait;

    /* @var Doctrine\Common\Persistence\ObjectManager */
    protected $objectManager;
    protected $document;
    protected $hydrator;
    protected $serializer;
    protected $serializerFormat = array('json', 'xml', 'yml');
    protected $predis;
    protected $logEntryDocument = 'Gedmo\\Loggable\\Document\\LogEntry';
    protected $logger;

    public function __construct($documentName = null, ObjectManager $objectManager)
    {
        if (!is_null($documentName)) {
            $this->setDocument($documentName);
        }

        $this->objectManager = $objectManager;
        $this->enableSoftDeleteableFilter(true);
    }

    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new DoctrineObject($this->objectManager, get_class($this->document));
        }
        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return AbstractDbMapper
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Uses the hydrator to convert the document to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $document
     * @return array
     */
    public function toArray($document, HydratorInterface $hydrator = null)
    {
        if (is_array($document)) {
            return $document; // cut down on duplicate code
        } elseif (is_object($document)) {
            if (!$hydrator) {
                $hydrator = $this->getHydrator();
            }
            return $hydrator->extract($document);
        }
        throw new Exception\InvalidArgumentException('Document passed to db mapper should be an array or object.');
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
     * @param array|\MdjamanCommon\Document\BaseDocument $document
     * @return string
     */
    public function serialize($document, $format = 'json')
    {
        if (!in_array($format, $this->serializerFormat)) {
            throw new Exception\InvalidArgumentException('Format ' . $format . ' is not valid');
        }
        $serializer = $this->getSerializer();
        $serialize  = $serializer->serialize($document, $format);

        if ($format == 'json') {
            $serialize = json_decode($serialize);
        }
        return $serialize;
    }

    /**
     * @param array $data
     * @param \MdjamanCommon\Document\BaseDocument $document
     * @return \MdjamanCommon\Document\BaseDocument
     */
    public function hydrate($data, $document = null)
    {
        if (is_null($document)) {
            $document = $this->createDocument();
        }

        # Gives the possibility to change $argv in listeners
        $argv = ['data' => &$data, 'document' => $document];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        $this->objectManager->getHydratorFactory()->hydrate($document, $data);

        $this->triggerEvent(__FUNCTION__.'.post', $argv);

        return $document;
    }

    /**
     * Creates a new instance of the given documentName or of the already known
     * one whose FQDN is stored in the className property.
     *
     * @return \MdjamanCommon\Document\BaseDocument
     * @throws \Exception
     */
    public function createDocument($documentName = null)
    {
        if (is_null($documentName)) {
            $documentName = $this->getDocument();
            if ( !$documentName) {
                // @todo throw good Exception
                throw new Exception\InvalidArgumentException("documentName not set. Can't create class.");
            }
        } else {
            if (false === class_exists($documentName)) {
                // @todo throw good Exception
                throw new Exception\InvalidArgumentException("'".$documentName."' class doesn't exist. Can't create class.");
            }
        }

        return new $documentName;
    }

    public function getRepository()
    {
        $class = get_class($this->getDocument());
        return $this->objectManager->getRepository($class);
    }

    /**
     * Return log entries
     * From Loggable behavioral extension for Gedmo
     *
     * @param BaseDocument $document
     * @return void
     */
    public function getLogEntries(ModelInterface $document)
    {
        $logEntryDocument = $this->objectManager->getRepository($this->logEntryDocument);
        return $logEntryDocument->getLogEntries($document);
    }

    /**
     * @param string $id
     * @return BaseDocument
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

        $document = $this->getRepository()->find($id);

        $this->triggerEvent(__FUNCTION__, ['document' => $document]);
        $this->triggerEvent(__FUNCTION__.'.post', ['id' => $id, 'document' => $document]);

        return $document;
    }

    /**
     * @param array $criteria
     * @return Document\Base
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

        $document = $this->getRepository()->findOneBy($criteria);

        $this->triggerEvent('find', ['document' => $document]);
        $this->triggerEvent(__FUNCTION__.'.post', ['criteria' => $criteria, 'document' => $document]);

        return $document;
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

        foreach ($entities as $document) {
            $this->triggerEvent('find', ['document' => $document]);
        }

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

        foreach ($entities as $document) {
            $this->triggerEvent('find', ['document' => $document]);
        }

        $this->triggerEvent(__FUNCTION__ . '.post', array_merge($argv, ['entities' => $entities]));

        return $entities;
    }

    /**
     * @param array|BaseDocument $document
     * @param bool $flush
     * @param string $event Overrides the default event name
     * @return Document\Base
     */
    public function save($document, $flush = false, $event = null)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['document' => &$document, 'flush' => &$flush];
        $this->triggerEvent(__FUNCTION__ . '.pre', $argv);
        extract($argv);

        if (is_array($document)) {
            # Means we only have an array of data here
            $data = $document;
            $document = null;
            if (array_key_exists('id', $data) && !empty($data['id'])) {
                # We have an id here > it's an update !
                $document = $this->find($data['id']);
                unset($data['id']);
            }
            $document = $this->hydrate($data, $document);
        }

        $this->objectManager->persist($document);

        if ($flush == true) {
            $this->objectManager->flush();
        }

        $this->triggerEvent($event ? $event : __FUNCTION__.'.post', array_merge($argv, ['saved' => $document]));

        return $document;
    }

    /**
     * @param string|array|BaseDocument $document
     * @param bool $flush
     * @return Document\Base
     */
    public function delete($document, $flush = false)
    {
        # Gives the possibility to change $argv in listeners
        $argv = ['document' => &$document, 'flush' => &$flush];
        $this->triggerEvent(__FUNCTION__.'.pre', $argv);
        extract($argv);

        if (is_string($document)) {
            # Means we only have the id of the document
            $document = $this->find($document);
        } else if (is_array($document)) {
            # Means we only have criteria precise enough to get the document
            $document = $this->findOneBy($document);
        }

        $this->objectManager->remove($document);

        if ($flush == true) {
            $this->objectManager->flush();
        }

        $this->triggerEvent(__FUNCTION__.'.post', array_merge($argv, ['deleted' => $document]));

        return $document;
    }
    
    /**
     * enable/disable documentManager softDeleteable
     * @param boolean $enable
     * @return void
     */
    public function enableSoftDeleteableFilter($enable = true)
    {
        if (true === $enable) {
            $this->objectManager->getFilterCollection()->enable('softDeleteable');
        } else {
            $this->objectManager->getFilterCollection()->disable('softDeleteable');
        }
    }

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
    
    public function getLogger()
    {
        if (!$this->logger) {
            $this->setLogger();
        }
        return $this->logger;
    }
}
