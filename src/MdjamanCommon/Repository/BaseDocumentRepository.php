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

namespace MdjamanCommon\Repository;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

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
        $qb->eagerCursor(true);
        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param array|null $criteria
     * @param null|string $sort
     * @param int $dir
     * @param null|int $limit
     * @param null|int $offset
     * @param array $params
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
        $qb = $this->createQueryBuilder()->eagerCursor(true);

        foreach ($criteria as $key => $value) {
            if ($key !== null) {
                $qb->addOr($qb->expr()->field($key)->equals(new \MongoRegex('/.*' . $value . '.*/i')));
            }
        }

        foreach ($params as $k => $val) {
            if ($k !== null) {
                $qb->field($k)->equals($val);
            }
        }

        if (null !== $sort) {
            $qb->sort($sort, $dir);
        }

        if ($limit !== null) {
            $qb->limit($limit)->skip($offset);
        }

        $cursor = $qb->getQuery()->toArray();

        $result = array();
        foreach ($cursor as $cur) {
            array_push($result, $cur);
        }

        return $result;
    }
}
