<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    static  $STATE_NEW = 1, // Новый
            $STATE_PROCESSED = 2, // Оформлен
            $STATE_PAID = 3, // Оплачен
            $STATE_CLOSED = 4, // Закрыт
            $STATE_CANCEL = 5; // Отмена


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }


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
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
