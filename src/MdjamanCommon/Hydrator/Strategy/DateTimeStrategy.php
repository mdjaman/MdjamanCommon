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

namespace MdjamanCommon\Hydrator\Strategy;

use Zend\Hydrator\Strategy\DefaultStrategy;

/**
 * Class DateTimeStrategy
 * @package MdjamanCommon\Hydrator\Strategy
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class DateTimeStrategy extends DefaultStrategy
{

    protected $allowNull;

    /**
     * DateTimeStrategy constructor.
     * @param bool $allowNull
     */
    public function __construct($allowNull = true)
    {
        $this->allowNull = (bool) $allowNull;
    }

    /**
     * {@inheritdoc}
     *
     * Convert a string value into a DateTime object
     */
    public function hydrate($value)
    {
        if (empty($value) && true === $this->allowNull) {
            $value = null;
        } elseif (is_string($value)) {
            $value = new \DateTime($value);
        }
        return $value;
    }
}
