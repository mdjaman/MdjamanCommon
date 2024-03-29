<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2023 Marcel DJAMAN
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
 * @copyright 2023 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Form;

use MdjamanCommon\EventManager\EventManagerAwareTrait;
use MdjamanCommon\Provider\ServiceManagerAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

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
     *
     * @param null $name
     * @param bool $actionsField
     * @param bool $crsfField
     */
    public function __construct($name = null, $actionsField = false, bool $crsfField = false)
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
