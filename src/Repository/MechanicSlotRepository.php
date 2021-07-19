<?php

namespace App\Repository;

use App\Entity\MechanicSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MechanicSlot|null find($id, $lockMode = null, $lockVersion = null)
 * @method MechanicSlot|null findOneBy(array $criteria, array $orderBy = null)
 * @method MechanicSlot[]    findAll()
 * @method MechanicSlot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MechanicSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MechanicSlot::class);
    }

    // /**
    //  * @return MechanicSlot[] Returns an array of MechanicSlot objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MechanicSlot
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
