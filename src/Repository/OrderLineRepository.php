<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method OrderLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderLine[]    findAll()
 * @method OrderLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderLine::class);
    }


    public function getOrderItemsAsArray(int $order, int $user=null, string $user_session_key=null, bool $array = true){
        if($order <= 0 || ($user == null && $user_session_key == null)) return [];

        $db = $this->createQueryBuilder('o', 'o.goods_id');
        $result = $db->select('o.id', 'o.order_id', 'o.goods_id', 'o.goods_qnt', 'o.goods_price')
            ->where('o.order_id = :order_id')
            ->setParameter('order_id', $order);

        if($user != null){
            $result = $result->innerJoin(Order::class, 'r', Join::WITH, 'r.user_id = :user_id')
                ->setParameter('user_id', $user);
        }
        else{
            $result = $result->innerJoin(Order::class, 'r', Join::WITH, 'r.user_session_key = :user_session_key')
                ->setParameter('user_session_key', $user_session_key);
        }

        $result = $result->getQuery();

        if($array == true){
            $result = $result->getArrayResult();
        }
        else{
            $result = $result->getResult();
        }

        return $result;
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

    // Получение основной информации о заказе
    public function getOrdersSum($order, int $user=null){
        if(is_array($order)){
            $order = array_diff(array_map('intval', $order), ['', 0]);
        }
        elseif(is_integer($order)){
            $order = intval($order);
        }
        else{
            return false;
        }

        $db = $this->createQueryBuilder('w', 'w.order_id');

        $rq = $db->select('w.order_id', 'SUM(w.goods_price) AS total')
            ->where('w.order_id IN (:order_id)')
            ->setParameter('order_id', $order)
            ->groupBy('w.order_id');


        if(intval($user) > 0) {
            $rq = $rq->innerJoin( Order::class, 'o', Join::ON, 'o.id IN (:order_id) AND o.user_id = :user_id' )->setParameters( [
                    'order_id' => $order,
                    'user_id'  => $user
                ] );
        }

        $result = $rq->getQuery()->getArrayResult();

        return $result;
    }

    // /**
    //  * @return OrderLine[] Returns an array of OrderLine objects
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
    public function findOneBySomeField($value): ?OrderLine
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
