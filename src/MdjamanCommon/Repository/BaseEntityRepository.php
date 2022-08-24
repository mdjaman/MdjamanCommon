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
