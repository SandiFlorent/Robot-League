<?php

namespace App\Repository;

use App\Entity\ChampionshipList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChampionshipList>
 */
class ChampionshipListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChampionshipList::class);
    }

    public function findPastChampionshipLists(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('c')
            ->andWhere('c.dateEnd < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function findFutureChampionshipLists(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('c')
            ->andWhere('c.dateStart > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function findActiveAndPastChampionshipLists(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('c')
            ->andWhere('c.dateStart <= :now')
            ->andWhere('c.dateEnd >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return ChampionshipList[] Returns an array of ChampionshipList objects
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

    //    public function findOneBySomeField($value): ?ChampionshipList
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
