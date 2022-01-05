<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2022 Marcel DJAMAN
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
 * @copyright 2022 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\DefaultStrategy;

/**
 * Class DateTimeStrategy
 * @package MdjamanCommon\Hydrator\Strategy
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class DateTimeStrategy extends DefaultStrategy
{

    /**
     * @var bool
     */
    protected bool $allowNull;


    /**
     * DateTimeStrategy constructor.
     *
     * @param bool $allowNull
     */
    public function __construct(bool $allowNull = true)
    {
        $this->allowNull = $allowNull;
    }

    /**
     * {@inheritdoc}
     *
     * Convert a string value into a DateTime object
     *
     * @throws \Exception
     */
    public function hydrate($value, ?array $data = null)
    {
        if (empty($value) && true === $this->allowNull) {
            $value = null;
        } elseif (is_string($value)) {
            $value = new \DateTime($value);
        }
        return $value;
    }
}
