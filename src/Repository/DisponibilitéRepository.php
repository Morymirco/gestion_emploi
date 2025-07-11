<?php

namespace App\Repository;

use App\Entity\Disponibilité;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Disponibilité>
 *
 * @method Disponibilité|null find($id, $lockMode = null, $lockVersion = null)
 * @method Disponibilité|null findOneBy(array $criteria, array $orderBy = null)
 * @method Disponibilité[]    findAll()
 * @method Disponibilité[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibilitéRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disponibilité::class);
    }

//    /**
//     * @return Disponibilité[] Returns an array of Disponibilité objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Disponibilité
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
