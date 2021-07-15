<?php

namespace App\Repository;

use App\Entity\MessageKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageKey[]    findAll()
 * @method MessageKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageKey::class);
    }

    // /**
    //  * @return MessageKey[] Returns an array of MessageKey objects
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
    public function findOneBySomeField($value): ?MessageKey
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
