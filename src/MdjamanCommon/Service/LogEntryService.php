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

namespace Application\Service;

use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Loggable\Entity\LogEntry;
use Zend\ServiceManager\ServiceManager;
use Doctrine\Common\Persistence\ObjectManager;

class LogEntryService extends AbstractService
{

    protected $allowed_filter = array('patient', 'visite', 'ets', 'prescripteur', 'personnel');

    protected $allowed_method = array('find', 'findAll', 'findBy', 'findOneBy');

    protected $userEntity = 'Identity\\Entity\\User';

    public function __construct(ServiceManager $serviceManager, ObjectManager $om)
    {
        parent::__construct(new LogEntry(), $om);

        $this->setServiceManager($serviceManager);
    }

    public function resultWrapper($resultset)
    {
        /*if ($resultset instanceof Cursor) {
            $resultset = $resultset->toArray();
        }*/

        $results = [];
        if ($resultset) {
            $filled = false;
            while (($log = array_pop($resultset)) && !$filled) {
                $wrapped = new EntityWrapper($log, $this->em);
                $objectMeta = $wrapped->getMetadata();

                if ($userData = $log->getUsername()) {
                    $field = 'username';
                    $userData = $userData ? $this->em->getReference($this->userEntity, $userData) : null;
                    $wrapped->setPropertyValue($field, $userData);
                }
                if ($objectClassData = $log->getObjectClass()) {
                    $field = 'objectClass';
                    $objectClassData = $objectClassData ?
                        $this->em->getReference($objectClassData, $log->getObjectId()) : null;
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
     * @return multitype:
     */
    public function filter(array $filters = null)
    {
        $filter = null;
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
            	$namespace = '\Application\\Entity';
            	if ( in_array($value, $this->allowed_filter) ) {
            		$class = $namespace . '\\' . ucfirst($value);
            		$criteria = ['objectClass' => get_class(new $class)];
            	}
            	$result = $this->findBy($criteria, $orderBy, $limit, $offset);
            	break;
            case 'action':
            	$criteria = ['action' => $value];
            	$result = $this->findBy($criteria, $orderBy, $limit, $offset);
            	break;
            default:
                if ( in_array($filter, $this->allowed_filter) ) {
                    $criteria = [$filter => $value];
                }
                $result = $this->findBy($criteria, $orderBy, $limit, $offset);
                break;
        }
        
        return $this->resultWrapper($result);
    }

}
