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

namespace MdjamanCommon\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use MdjamanCommon\Model\ModelInterface;
use MdjamanCommon\Traits\SoftDeleteableEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class BaseEntity implements ModelInterface
{
    
    use SoftDeleteableEntity;

    protected $__fields;
    protected $__name;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @JMS\Groups({"list", "details"})
     */
    protected $created_at;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     * @JMS\Groups({"list", "details"})
     */
    protected $updated_at;


    public abstract function getId();

    public function __construct()
    {
        $reflection = new \ReflectionClass($this);
        $this->__name = get_class($this);
        $vars = $reflection->getDefaultProperties();
        foreach ($vars as $name => $val) {
            if (substr($name, 0, 1) !== '_') {
                $this->__fields[] = strtolower($name);
            }
        }
    }

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

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

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
            if (in_array($key, $this->__fields)) {
                if (is_array($val)) {
                    $iterator = new \RecursiveArrayIterator($val);
                    if ($iterator->hasChildren()) {
                        continue;
                    }
                }
                $this->$key = $val;
            }
        }
    }

    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);
        return $obj_vars;
    }

    public function toArray()
    {
        $data = array();
        foreach ($this->__fields as $field) {
            $data[$field] = $this->$field;
        }
        return (count($data) > 0) ? $data : null;
    }

    public function __toString()
    {
        //return "[" . get_class($this) . " #" . $this->getId() . "]";
         return $this->getId();
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
}
