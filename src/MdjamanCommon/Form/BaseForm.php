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

namespace MdjamanCommon\Form;

use MdjamanCommon\EventManager\EventManagerAwareTrait;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;
use Zend\Form\Element\Csrf;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Class BaseForm
 * @package MdjamanCommon\Form
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class BaseForm extends Form
{

    use ServiceManagerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * BaseForm constructor.
     * @param null $name
     * @param bool $actionsField
     * @param bool $crsfField
     */
    public function __construct($name = null, $actionsField = false, $crsfField = false)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        if ($actionsField) {
            $actions = new Fieldset('actions');
            $actions->add(array(
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit', 
                    'value' => 'Enregistrer', 
                    'class' => 'btn btn-primary'
                ],
                'options' => array('label' => 'Enregistrer')
            ));
            $actions->add([
                'name' => 'reset',
                'attributes' => [
                    'type' => 'reset', 
                    'value' => 'Annuler', 
                    'class' => 'btn'
                ],
                'options' => ['label' => 'Annuler']
            ]);
            $this->add($actions, [
                'priority' => -100,
            ]);
        }

        $this->add([
            'name' => 'token',
            'type' => 'Hidden',
            'attributes' => [
                'type' => 'hidden'
            ],
        ]);

        if ($crsfField === true) {
            $csrf = new Csrf('csrf');
            $this->add($csrf);
        }
    }
}
