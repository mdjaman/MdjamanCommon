<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2022 Marcel DJAMAN
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
 * @copyright 2022 Marcel DJAMAN
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

namespace MdjamanCommon\Service;

/**
 * Interface SearchServiceInterface
 *
 * @package MdjamanCommon\Service
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
interface SearchServiceInterface
{
    /**
     * Performs a search
     *
     * @param string $query
     * @param null $limit
     * @param int $offset
     * @return
     */
    public function search(string $query, $limit = null, int $offset = 0);

    /**
     * Save a user search to redis db
     *
     * @param string $query
     * @param $user
     */
    public function saveSearch(string $query, $user);

    /**
     * Get a user saved searches
     *
     * @param string $user
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getUserSearch(string $user, int $limit = null, ?int $offset = 0): array;

    /**
     * @return mixed
     */
    public function getClient();

    /**
     * @param array|null $config
     * @return $this
     */
    public function setClient(array $config = null);

    /**
     * @param string $index
     * @return $this
     */
    public function setIndex(string $index);

    /**
     * Get predis client
     */
    public function getPredis();

    /**
     * Set predis client
     *
     * @param array|null $parameters the connection parameters
     * @param array|null $options the profile options
     * @return $this
     */
    public function setPredis(array $parameters = null, array $options = null);
}
