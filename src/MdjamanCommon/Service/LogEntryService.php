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

use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Interop\Container\ContainerInterface;
use MdjamanCommon\Options\ModuleOptionsInterface;

/**
 * Class LogEntryService
 * @package MdjamanCommon\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class LogEntryService extends AbstractService implements LogEntryServiceInterface
{
    /**
     * @var array
     */
    protected $allowed_method = ['find', 'findAll', 'findBy', 'findOneBy'];

    /**
     * @var string
     */
    protected $userEntity;

    /**
     * @var array
     */
    protected $allowed_filter = [];

    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     * LogEntryService constructor.
     * @param ContainerInterface $container
     * @param ObjectManager $om
     * @param ModuleOptionsInterface $moduleOptions
     */
    public function __construct(ContainerInterface $container, ObjectManager $om, ModuleOptionsInterface $moduleOptions)
    {
        $entityClass = $moduleOptions->getLogEntryEntityClass();
        parent::__construct(new $entityClass, $om);

        $this->options = $moduleOptions;
        $this->userEntity = $moduleOptions->getUserEntityClass();
    }

    /**
     * @param mixed $resultset
     * @return array
     */
    public function resultWrapper($resultset)
    {
        /*if ($resultset instanceof Cursor) {
            $resultset = $resultset->toArray();
        }*/

        $results = [];
        if ($resultset) {
            $filled = false;
            while (($log = array_pop($resultset)) && ! $filled) {
                $wrapped = new EntityWrapper($log, $this->objectManager);
                //$objectMeta = $wrapped->getMetadata();

                if ($userData = $log->getUsername()) {
                    $field = 'username';
                    $userData = $userData ? $this->objectManager->getReference($this->userEntity, $userData) : null;
                    $wrapped->setPropertyValue($field, $userData);
                }
                if ($objectClassData = $log->getObjectClass()) {
                    $field = 'objectClass';
                    $objectClassData = $objectClassData ?
                        $this->objectManager->getReference($objectClassData, $log->getObjectId()) : null;
                    $wrapped->setPropertyValue($field, $objectClassData);
                }

                $results[] = $wrapped;
                $filled = count($log) === 0;
            }
        }

        return $results;
    }

    /**
     * Filter
     * @param array $filters
     * @return mixed
     */
    public function filter(array $filters = null)
    {
        $filter = null;
        $value = null;
        $criteria = [];
        $limit = 20;
        $sort_df = 'loggedAt';
        $offset = null;

        if (is_array($filters)) {
            extract($filters, EXTR_OVERWRITE);
        }

        $sort = !isset($sort) ? $sort_df : $sort;

        if (!isset($dir) || !in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $orderBy = [$sort => $dir];

        switch ($filter) {
            case 'class':
            	if (array_key_exists($value, $this->allowed_filter) ) {
            		$class = $this->allowed_filter[$value];
            		$criteria = ['objectClass' => $class];
            	}
            	$result = $this->findBy($criteria, $orderBy, $limit, $offset);
            	break;
            case 'action':
            	$criteria = ['action' => $value];
            	$result = $this->findBy($criteria, $orderBy, $limit, $offset);
            	break;
            default:
                /*if (in_array($filter, $this->allowed_filter)) {
                    $criteria = [$filter => $value];
                }*/
                $result = $this->findBy($criteria, $orderBy, $limit, $offset);
                break;
        }
        
        return $this->resultWrapper($result);
    }
}
