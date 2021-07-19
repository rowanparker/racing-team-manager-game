<?php

namespace App\Repository;

use App\Entity\DriverSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DriverSlot|null find($id, $lockMode = null, $lockVersion = null)
 * @method DriverSlot|null findOneBy(array $criteria, array $orderBy = null)
 * @method DriverSlot[]    findAll()
 * @method DriverSlot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverSlot::class);
    }

    // /**
    //  * @return DriverSlot[] Returns an array of DriverSlot objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DriverSlot
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
