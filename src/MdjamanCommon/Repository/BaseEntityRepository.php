<?php

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
     * @param $criteria
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
            ->useResultCache(true, 3600, $cacheKey);

        return $query->getSingleScalarResult();
    }
}
