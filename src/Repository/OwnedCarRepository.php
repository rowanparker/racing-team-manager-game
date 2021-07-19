<?php

namespace App\Repository;

use App\Entity\OwnedCar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OwnedCar|null find($id, $lockMode = null, $lockVersion = null)
 * @method OwnedCar|null findOneBy(array $criteria, array $orderBy = null)
 * @method OwnedCar[]    findAll()
 * @method OwnedCar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OwnedCarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OwnedCar::class);
    }

    // /**
    //  * @return OwnedCar[] Returns an array of OwnedCar objects
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
    public function findOneBySomeField($value): ?OwnedCar
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
