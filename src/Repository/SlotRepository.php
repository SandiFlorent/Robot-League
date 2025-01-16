<?php

namespace App\Repository;

use App\Entity\Slot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ChampionshipList;

/**
 * @extends ServiceEntityRepository<Slot>
 */
class SlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slot::class);
    }

    //    /**
    //     * @return Slot[] Returns an array of Slot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Slot
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // SlotRepository.php
    public function findOverlappingSlots(\DateTimeInterface $dateDebut, \DateTimeInterface $dateEnd, ?ChampionshipList $championshipList): array
    {
        $qb = $this->createQueryBuilder('s');

        return $qb->where('s.championshipList = :championshipList')
            ->andWhere('(:dateDebut BETWEEN s.dateDebut AND s.dateEnd OR :dateEnd BETWEEN s.dateDebut AND s.dateEnd)')
            ->orWhere('s.dateDebut BETWEEN :dateDebut AND :dateEnd')
            ->setParameter('championshipList', $championshipList)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateEnd', $dateEnd)
            ->getQuery()
            ->getResult();
    }
}
