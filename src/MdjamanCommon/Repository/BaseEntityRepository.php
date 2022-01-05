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

use Doctrine\ORM\EntityRepository;

/**
 * Class BaseEntityRepository
 * @package MdjamanCommon\Repository
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
class BaseEntityRepository extends EntityRepository
{

    /**
     * Count query row results after applied criteria
     *
     * @param array|null $criteria
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countResult(array $criteria = null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)');

        if (count($criteria)) {
            $x = 1;
            foreach ($criteria as $key => $value) {
                if ($key !== '') {
                    $qb->andWhere("e.$key = ?$x");
                    $qb->setParameter($x, $value);
                    ++$x;
                }
            }
        }

        $query = $qb->getQuery();

        $cacheKey = md5(__FUNCTION__ . json_encode(func_get_args()));
        $query->useQueryCache(true)
            ->enableResultCache(3600, $cacheKey);

        return $query->getSingleScalarResult();
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
        $qb = $this->createQueryBuilder('e');

        $x = 1;
        foreach ($criteria as $key => $value) {
            if ($key !== null) {
                $qb->orWhere("e.$key = ?$x");
                $qb->setParameter($x, $value);
                ++$x;
            }
        }

        foreach ($params as $k => $val) {
            if ($k !== null) {
                $qb->andWhere("e.$k = ?$x");
                $qb->setParameter($x, $val);
                ++$x;
            }
        }

        $qb->addOrderBy('e.' . $sort, $dir);

        $query = $qb->getQuery();

        if (null !== $limit) {
            $query->setMaxResults($limit);
            if (null !== $offset) {
                $query->setFirstResult($offset);
            }
        }

        return $qb->getResult();
    }
}
