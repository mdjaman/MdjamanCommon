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
    protected $allowed_method = array('find', 'findAll', 'findBy', 'findOneBy');

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

        $this->setContainer($container);
    }

    /**
     * @param $resultset
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
            while (($log = array_pop($resultset)) && !$filled) {
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
