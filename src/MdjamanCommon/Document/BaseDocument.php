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

namespace MdjamanCommon\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;
use MdjamanCommon\Model\ModelInterface;
use MdjamanCommon\Traits\SoftDeleteableDocument;

/**
 * @ODM\MappedSuperclass
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deleted_at", timeAware=false)
 */
abstract class BaseDocument implements ModelInterface
{

    use SoftDeleteableDocument;

    /**
     * @var \DateTime
     * @ODM\Field(name="created_at", type="date")
     * @JMS\Groups({"list", "details"})
     */
    protected $created_at;

    /**
     * @var \DateTime
     * @ODM\Field(name="updated_at", type="date")
     * @JMS\Groups({"list", "details"})
     */
    protected $updated_at;

    /**
     * @return string
     */
    abstract public function getId();

    /**
     * @ODM\PrePersist
     * @return $this
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime("now");
        return $this;
    }

    /**
     * @ODM\PreUpdate
     * @return $this
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime("now");
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param array<string, mixed> $data
     * @return $this
     * @throws \ReflectionException
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, $this->getClassFields())) {
                $this->$key = $val;
            }
        }
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @return array<string, mixed>
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->getClassFields() as $field) {
            $data[$field] = $this->$field;
        }
        return (count($data) > 0) ? $data : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
         return (string) $this->getId();
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $created_at
     * @return BaseDocument
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updated_at
     * @return BaseDocument
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class($this);
    }

    /**
     * @return string[]
     * @throws \ReflectionException
     */
    public function getClassFields()
    {
        $reflection = new \ReflectionClass($this);
        $vars = $reflection->getDefaultProperties();
        $fields = [];
        foreach ($vars as $name => $val) {
            if (substr($name, 0, 1) !== '_') {
                $fields[] = $name;
            }
        }
        return $fields;
    }
}
