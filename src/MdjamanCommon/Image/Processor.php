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
