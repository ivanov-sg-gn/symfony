<?php

namespace App\Repository;

use App\Entity\Goods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Goods|null find($id, $lockMode = null, $lockVersion = null)
 * @method Goods|null findOneBy(array $criteria, array $orderBy = null)
 * @method Goods[]    findAll()
 * @method Goods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GoodsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goods::class);
    }


    // Получение по массиву
    public function findByArray(array $filter = [], string $key='id'){

        $db = $this->createQueryBuilder('w', 'w.' . $key);

        $rq = null;

        foreach ($filter as $key => $value){
            if(is_array($value))
                if(!empty($rq))
                    $rq = $db->andWhere("w.$key IN (:$key)")->setParameter($key, $value);
                else $rq = $db->where("w.$key IN (:$key)")->setParameter($key, $value);
            elseif(is_integer($value) || is_string($value))
                if(!empty($rq))
                    $rq = $db->andWhere("w.$key = :$key")->setParameter($key, $value);
                else $rq = $db->where("w.$key = :$key")->setParameter($key, $value);
            elseif(is_bool($value))
                if(!empty($rq))
                    $rq = $db->andWhere("w.$key = :$key")->setParameter($key, boolval($value));
                else  $rq = $db->where("w.$key = :$key")->setParameter($key, boolval($value));
        }

        return $rq->getQuery()->getArrayResult();

    }

    // /**
    //  * @return Goods[] Returns an array of Goods objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Goods
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
