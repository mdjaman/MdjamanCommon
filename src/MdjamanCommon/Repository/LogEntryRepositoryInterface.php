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

namespace MdjamanCommon\Repository;

use Gedmo\Loggable\Entity\LogEntry;

/**
 * Interface LogEntryRepositoryInterface
 *
 * @package MdjamanCommon\Repository
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
interface LogEntryRepositoryInterface
{

    /**
     * Loads all log entries for the
     * given $document
     *
     * @param object $document
     *
     * @return \Gedmo\Loggable\Document\LogEntry[]|\Gedmo\Loggable\Document\LogEntry[]
     */
    public function getLogEntries($document);

    /**
     * Reverts given $document to $revision by
     * restoring all fields from that $revision.
     * After this operation you will need to
     * persist and flush the $document.
     *
     * @param object $document
     * @param int    $version
     *
     * @throws \Gedmo\Exception\UnexpectedValueException
     *
     * @return void
     */
    public function revert($document, $version = 1);
}
