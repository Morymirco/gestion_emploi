<?php

namespace App\Repository;

use App\Entity\Département;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Département>
 *
 * @method Département|null find($id, $lockMode = null, $lockVersion = null)
 * @method Département|null findOneBy(array $criteria, array $orderBy = null)
 * @method Département[]    findAll()
 * @method Département[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DépartementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Département::class);
    }

//    /**
//     * @return Département[] Returns an array of Département objects
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

//    public function findOneBySomeField($value): ?Département
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
