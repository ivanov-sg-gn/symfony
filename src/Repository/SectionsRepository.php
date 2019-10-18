<?php

namespace App\Repository;

use App\Entity\Sections;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Sections|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Sections|null findOneBy( array $criteria, array $orderBy = null )
 * @method Sections[]    findAll()
 * @method Sections[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class SectionsRepository extends ServiceEntityRepository {
    public function __construct ( ManagerRegistry $registry ) {
        parent::__construct( $registry, Sections::class );
    }

    public function findAllSectionsByLinks ( array $sections ) {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();

        # Первая секция
        $first_elem = reset($sections);
        $query = $qb->select('q0.name')
            ->from('\App\Entity\Sections', 'q0')
            ->where($qb->expr()->eq('q0.code', "'$first_elem'"))
        ;

        # остальные секции
        foreach ($sections as $key => $val){
            if($key == 0) continue;

            $query->innerJoin('\App\Entity\Sections', 'q'.$key, 'WITH', 'q'.$key.'.parent = q'.($key-1).'.id')
                ->andWhere($qb->expr()->eq('q'.$key.'.code', "'$val'"))
            ;
        }

        $res = $query->getQuery()->getOneOrNullResult();

        if( !empty($res) ){
            # Достаём инфу о секциях
            $arRes = $qb->select('s')
                ->from('\App\Entity\Sections', 's')
                ->where($qb->expr()->in('s.code', $sections))
                ->getQuery()
                ->getResult()
            ;

            return $arRes;
        }

        return $res;
    }


    // /**
    //  * @return Sections[] Returns an array of Sections objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sections
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
