<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Marcel Djaman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MdjamanCommon\Form;

use Zend\Form\Form;
use Zend\InputFilter;

/**
 * Class UploadForm
 * @package Application\Form
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
