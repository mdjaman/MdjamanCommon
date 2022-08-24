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

namespace MdjamanCommon\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class PredisOptions
 * @package MdjamanCommon\Options
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class PredisOptions extends AbstractOptions
{

    /**
     * @var array
     */
    protected $defaultParameter = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 15,
        'alias' => 'default',
    ];

    /**
     * @var array
     */
    protected $defaultSettings = [];

    /**
     * @return array
     */
    public function getDefaultParameter()
    {
        return $this->defaultParameter;
    }

    /**
     * @param array $defaultParameter
     */
    public function setDefaultParameter(array $defaultParameter)
    {
        $this->defaultParameter = $defaultParameter;
    }

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return $this->defaultSettings;
    }

    /**
     * @param array $defaultSettings
     */
    public function setDefaultSettings(array $defaultSettings)
    {
        $this->defaultSettings = $defaultSettings;
    }
}
