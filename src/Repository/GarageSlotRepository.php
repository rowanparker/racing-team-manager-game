<?php

namespace App\Repository;

use App\Entity\GarageSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GarageSlot|null find($id, $lockMode = null, $lockVersion = null)
 * @method GarageSlot|null findOneBy(array $criteria, array $orderBy = null)
 * @method GarageSlot[]    findAll()
 * @method GarageSlot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GarageSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GarageSlot::class);
    }

    // /**
    //  * @return GarageSlot[] Returns an array of GarageSlot objects
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
    public function findOneBySomeField($value): ?GarageSlot
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
