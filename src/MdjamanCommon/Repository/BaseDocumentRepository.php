<?php

namespace MdjamanCommon\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

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
