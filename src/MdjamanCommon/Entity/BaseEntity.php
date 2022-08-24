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
    abstract  public function getId();

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime("now");
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
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
     * Update attributes of an entity by array
     *
     * @param array $data
     * @return mixed|void
     * @throws \ReflectionException
     */
    public function exchangeArray($data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, $this->getClassFields())) {
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
        foreach ($this->getClassFields() as $field) {
            $data[$field] = $this->$field;
        }
        return (count($data) > 0) ? $data : null;
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
