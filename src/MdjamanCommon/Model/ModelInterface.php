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

namespace MdjamanCommon\Model;

/**
 * Interface ModelInterface
 * @package MdjamanCommon\Model
 */
interface ModelInterface
{
    /**
     * @ORM\PrePersist
     */
    public function PrePersist();

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate();

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt();

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt();

    /**
     * @param array $data
     * @return mixed
     */
    public function exchangeArray($data);

    /**
     * @return array
     */
    public function getArrayCopy();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function __toString();

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     * @return BaseEntity
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     * @return BaseEntity
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getDocumentName();

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getDocumentFields();
}
