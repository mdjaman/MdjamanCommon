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

namespace MdjamanCommon\Utils;

/**
 * Class StringUtils
 *
 * @package MdjamanCommon\Utils
 */
class StringUtils
{
    /**
     * @param string $type
     * @param int $length
     * @return string
     */
    public static function randomString($type = 'alnum', $length = 8)
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string)$type;
                break;
        }

        $crypto_rand_secure = function ($min, $max) {
            $range = $max - $min;
            if ($range < 0) {
                return $min;
            }
            // not so random...
            $log = log($range, 2);
            $bytes = (int) ($log / 8) + 1; // length in bytes
            $bits = (int)$log + 1; // length in bits
            $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);

            return $min + $rnd;
        };

        $token = "";
        $max = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $token .= $pool[$crypto_rand_secure(0, $max)];
        }
        return $token;
    }

    /**
     * Truncate text
     *
     * @param string $text
     * @param int $length
     * @param string $ending
     * @param bool $exact
     * @param bool $considerHtml
     * @return string
     */
    public static function truncate($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
    {
        $open_tags = [];
        if ($considerHtml) {
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            $total_length = strlen($ending);
            $truncate = '';

            $htmlPattern =
            '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is';
            foreach ($lines as $line_matchings) {
                if (! empty($line_matchings[1])) {
                    if (preg_match(
                        '/^<\s*\/([^\s]+?)\s*>$/s',
                        $line_matchings[1],
                        $tag_matchings
                    )) {
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    } elseif (preg_match(
                        '/^<s*([^s>!]+).*?>$/s',
                        $line_matchings[1],
                        $tag_matchings
                    )) {
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    $truncate .= $line_matchings[1];
                }
                $content_length = strlen(
                    preg_replace(
                        '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i',
                        ' ',
                        $line_matchings[2]
                    )
                );
                if ($total_length + $content_length > $length) {
                    $left = $length - $total_length;
                    $entities_length = 0;
                    if (preg_match_all(
                        '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i',
                        $line_matchings[2],
                        $entities,
                        PREG_OFFSET_CAPTURE
                    )) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }

        if (! $exact) {
            $spacepos = strrpos($truncate, ' ');
            if ($spacepos) {
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        $truncate .= $ending;

        if ($considerHtml) {
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    /**
     * Converts a CamelCase word into snake_case
     *
     * @param string $input - string to convert to snake_case
     * @param string|null $splitter - splitter to use
     * @return string        - string converted to snake_case
     * @since  12/02/2014 - v.1.0.0
     *
     * @author Exadra37 exadra37 in gmail point com
     */
    public static function camelCaseToSnakeCase(string $input, ?string $splitter = "_")
    {
        return ctype_lower($input) ?
            $input :
            str_replace(
                array("-", "_", "{$splitter}{$splitter}"),
                $splitter,
                ltrim(strtolower(preg_replace("/[A-Z]/", "{$splitter}$0", $input)), $splitter)
            );
    }

    /**
     * Converts camelCase string to have spaces between each.
     * @param string $camelCaseString
     * @return string
     */
    public static function camelCaseToTitle(string $camelCaseString)
    {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        if (! $a) {
            return '';
        }
        return join($a, []);
    }

    /**
     * Returns a camelCase version of the string. Trims surrounding spaces,
     * capitalizes letters following digits, spaces, dashes and underscores,
     * and removes spaces, dashes, as well as underscores.
     *
     * @param string $string
     * @param string|null $encoding
     * @return string
     */
    public static function camelize(string $string, ?string $encoding = null)
    {
        $stringy = lcfirst(trim($string));
        $stringy = preg_replace('/^[-_]+/', '', $stringy);
        $stringy = preg_replace_callback(
            '/[-_\s]+(.)?/u',
            function ($match) use (&$encoding) {
                if (isset($match[1])) {
                    return mb_strtoupper($match[1], $encoding);
                }
                return '';
            },
            $stringy
        );
        $stringy = preg_replace_callback(
            '/[\d]+(.)?/u',
            function ($match) use (&$encoding) {
                return mb_strtoupper($match[0], $encoding);
            },
            $stringy
        );

        return $stringy;
    }

    /**
     * Slugify text
     *
     * @param string $text
     * @return mixed|string
     */
    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
