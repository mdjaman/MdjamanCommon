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

namespace MdjamanCommon\Model;

/**
 * Interface ModelInterface
 * @package MdjamanCommon\Model
 */
interface ModelInterface
{
    public function prePersist();

    public function preUpdate();

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getClassFields();
}
