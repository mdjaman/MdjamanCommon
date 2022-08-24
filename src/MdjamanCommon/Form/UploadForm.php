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

use Zend\Form\Form;
use Zend\InputFilter;

/**
 * Class UploadForm
 * @package MdjamanCommon\Form
 */
class UploadForm extends Form
{
    /**
     * UploadForm constructor.
     * @param string $name
     */
    public function __construct($name = 'form-upload')
    {
        parent::__construct($name);
        
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * @return void
     */
    public function addElements()
    {
        $this->add(array(
            'name' => 'file',
            'type' => 'File',
            'attributes' => array(
                'id' => 'file',
                'multiple' => true,
            ),
            'options' => array(
                'label' => 'Fichiers',
            )
        ));
    }

    /**
     * @return void
     */
    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        $fileInput = new InputFilter\FileInput('file');
        $fileInput->setRequired(true);

        $fileInput->getValidatorChain()
                  ->attachByName('filesize', array('max' => 51200000));
                  //->attachByName('filemimetype', array('mimeType' => 'image/png,image/x-png,image/jpeg'));
                  //->attachByName('fileimagesize', array('maxWidth' => 100, 'maxHeight' => 100));

        $fileInput->getFilterChain()->attachByName(
            'filerename',
            array(
                'target' =>  './uploads/files/',
                'randomize' => true,
            )
        );
        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }
}
