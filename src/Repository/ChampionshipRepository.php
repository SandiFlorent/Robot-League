<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\ChampionshipList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Championship>
 */
class ChampionshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Championship::class);
    }


    public function getLastRound(ChampionshipList $championshipList): int
    {
        return $this->createQueryBuilder('c')
            ->select('MAX(c.round)')
            ->where('c.championshipList = :list')
            ->setParameter('list', $championshipList)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

//    /**
//     * @return Championship[] Returns an array of Championship objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Championship
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
