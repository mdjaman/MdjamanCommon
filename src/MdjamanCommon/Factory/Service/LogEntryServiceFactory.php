<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Marcel Djaman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MdjamanCommon\Factory\Service;

use MdjamanCommon\Service\LogEntryService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class LogEntryServiceFactory
 * @package MdjamanCommon\Factory\Service
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class LogEntryServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return LogEntryService
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $om = $sl->has('doctrine.entitymanager.orm_default') ? $sl->get('doctrine.entitymanager.orm_default') : $sl->get('doctrine.documentmanager.odm_default');
        $options = $sl->get('MdjamanCommon\Options\ModuleOptions');
        return new LogEntryService($sl, $om, $options);
    }

}
