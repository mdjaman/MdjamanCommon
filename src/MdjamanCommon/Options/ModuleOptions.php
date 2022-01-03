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

namespace MdjamanCommon\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 * @package MdjamanCommon\Options
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class ModuleOptions extends AbstractOptions implements ModuleOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    
    /**
     * @var string
     */
    protected $logEntryEntityClass = 'MdjamanCommon\Entity\LogEntry';

    /**
     * @var string
     */
    protected $userEntityClass = 'Identity\Entity\User';

    /**
     * @return string
     */
    public function getLogEntryEntityClass()
    {
        return $this->logEntryEntityClass;
    }

    /**
     * @param string $logEntryEntityClass
     * @return ModuleOptions
     */
    public function setLogEntryEntityClass($logEntryEntityClass)
    {
        $this->logEntryEntityClass = $logEntryEntityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserEntityClass()
    {
        return $this->userEntityClass;
    }

    /**
     * @param string $userEntityClass
     * @return ModuleOptions
     */
    public function setUserEntityClass($userEntityClass)
    {
        $this->userEntityClass = $userEntityClass;
        return $this;
    }
}
