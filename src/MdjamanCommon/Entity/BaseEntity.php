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

namespace MdjamanCommon\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use MdjamanCommon\Model\ModelInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class BaseEntity implements ModelInterface
{
    
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @JMS\Groups({"list", "details"})
     */
    protected $created_at;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     * @JMS\Groups({"details"})
     */
    protected $updated_at;

    /**
     * @return int|string
     */
    public abstract function getId();

    /**
     * @ORM\PrePersist
     */
    public function PrePersist()
    {
        $this->created_at = new \DateTime("now");
    }

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate()
    {
        $this->updated_at = new \DateTime("now");
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * update attributes of an entity by array
     */
    public function exchangeArray($data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, $this->getObjectFields())) {
                $this->$key = $val;
            }
        }
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @return array|null
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->getObjectFields() as $field) {
            $data[$field] = $this->$field;
        }
        return (count($data) > 0) ? $data : null;
    }

    /**
     * @return int|string
     */
    public function __toString()
    {
         return (string) $this->getId();
    }

    /**
     * Set createdAt
     *
     * @param \Datetime $createdAt
     * @return BaseEntity
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \Datetime $updatedAt
     * @return BaseEntity
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
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
     * @return array
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
