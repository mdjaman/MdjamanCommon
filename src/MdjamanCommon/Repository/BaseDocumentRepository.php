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

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use MongoDB\BSON\Regex;

/**
 * Class BaseDocumentRepository
 * @package MdjamanCommon\Repository
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class BaseDocumentRepository extends DocumentRepository
{
    /**
     * Count query row results after applied criteria
     *
     * @param array|null $criteria
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function countResult(array $criteria = null)
    {
        $qb = $this->createQueryBuilder();

        if (count($criteria)) {
            foreach ($criteria as $key => $value) {
                if ($key !== '') {
                    $qb->field($key)->equals($value);
                }
            }
        }

        $qb->count();
        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * A "contains, ignoring case" expression that matches $term literally: what a search box needs, so a dot, a star
     * or an unbalanced parenthesis the user typed is text, not pattern syntax.
     *
     * @param string $term
     * @return Regex
     */
    public static function containsRegex(string $term): Regex
    {
        return new Regex(preg_quote($term), 'i');
    }

    /**
     * Documents where ANY of the criteria fields contains its text (ignoring case), narrowed by $params (exact
     * matches), sorted and paged.
     *
     * @param array|null $criteria field => text
     * @param null|string $sort
     * @param int $dir
     * @param null|int $limit
     * @param null|int $offset
     * @param array $params field => exact value
     * @return array
     */
    public function fullSearchText(
        array $criteria = null,
        $sort = null,
        $dir = 1,
        $limit = null,
        $offset = null,
        $params = []
    ) {
        $qb = $this->buildFullSearchTextQuery($criteria, $sort, $dir, $limit, $offset, $params);

        return array_values($qb->getQuery()->toArray());
    }

    /**
     * The query fullSearchText() runs, apart so it can be checked without a database.
     */
    protected function buildFullSearchTextQuery(
        array $criteria = null,
        $sort = null,
        $dir = 1,
        $limit = null,
        $offset = null,
        $params = []
    ): Builder {
        $qb = $this->createQueryBuilder();

        foreach ((array) $criteria as $key => $value) {
            if ($key !== null) {
                $qb->addOr($qb->expr()->field($key)->equals(self::containsRegex((string) $value)));
            }
        }

        foreach ((array) $params as $k => $val) {
            if ($k !== null) {
                $qb->field($k)->equals($val);
            }
        }

        if (null !== $sort) {
            $qb->sort($sort, $dir);
        }

        if ($limit !== null) {
            $qb->limit($limit)->skip((int) $offset);
        }

        return $qb;
    }
}
