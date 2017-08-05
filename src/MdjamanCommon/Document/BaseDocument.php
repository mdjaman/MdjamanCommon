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

namespace MdjamanCommon\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MdjamanCommon\Model\ModelInterface;

/**
 * @ODM\MappedSuperclass
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class BaseDocument implements ModelInterface
{

    use SoftDeleteableDocument;

    protected $__fields;
    protected $__name;

    /**
     * @var \DateTime
     * @ODM\Field(name="created_at", type="date")
     */
    protected $created_at;

    /**
     * @var \DateTime
     * @ODM\Field(name="updated_at", type="date")
     */
    protected $updated_at;

    /**
     * @return string
     */
    public abstract function getId();

    /**
     * BaseDocument constructor.
     */
    public function __construct()
    {
        $reflection = new \ReflectionClass($this);
        $this->__name = get_class($this);
        $vars = $reflection->getDefaultProperties();
        foreach ($vars as $name => $val) {
            if (substr($name, 0, 1) !== '_')
                $this->__fields[] = strtolower($name);
        }
    }

    /**
     * @ODM\PrePersist
     */
    public function PrePersist()
    {
        $this->created_at = new \DateTime("now");
    }

    /**
     * @ODM\PreUpdate
     */
    public function PreUpdate()
    {
        $this->updated_at = new \DateTime("now");
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

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @return array|null
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->__fields as $field) {
            $data[$field] = $this->$field;
        }
        return (count($data) > 0) ? $data : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
         return $this->getId();
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

}
