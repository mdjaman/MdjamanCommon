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

namespace MdjamanCommon\Image;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

/**
 * Class Processor
 * @package MdjamanCommon\Image
 */
class Processor
{
    /**
     * @var array
     */
    protected $defaultSizes = [
        'mini' => [24, 24],
        'thumb' => [60, 60],
    ];

    /**
     *
     * Resize and create thumbnail from image source
     * @param string $path
     * @param array $resizes list Dimension of new images in array(width, height)
     */
    public function __construct($path, $resizes = array())
    {
        $path = realpath($path);
        $basename = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $imagine = new Imagine();

        $file = $imagine->open($path);
        $copy = $file->copy();

        if (!count($resizes)) {
            $resizes = $this->defaultSizes;
        }

        foreach ($resizes as $key => $value) {
            if (!count($value) > 1) {
                continue;
            }

            if ($key !== 'resize') {
                $newFileName = sprintf('%s_%s.%s', $filename, $key, $ext);
                $newPath = $basename . '/' . $newFileName;
                $resize = new Box($value[0], $value[1]);
                $copy->thumbnail($resize)
                     ->save($newPath);
            } else {
                $resize = new Box($value[0], $value[1]);
                $copy->resize($resize)
                     ->save($path);
            }
        }
    }

}
