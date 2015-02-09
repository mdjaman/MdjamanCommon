<?php

namespace Application\Options;

use Zend\Stdlib\AbstractOptions;

class PredisOptions extends AbstractOptions
{

    protected $defaultParameter = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 15,
        'alias' => 'default',
    ];
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
