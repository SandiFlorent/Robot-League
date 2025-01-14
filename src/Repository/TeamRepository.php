<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\ChampionshipList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findAllOrdered(ChampionshipList $championshiplist = null)
    {
        // Créez la base de la requête
        $queryBuilder = $this->createQueryBuilder('t')
            ->orderBy('t.score', 'DESC')               // Order by score, descending
            ->addOrderBy('t.goalAverage', 'DESC')      // Then order by goalAverage, descending
            ->addOrderBy('t.inscriptionDate', 'ASC');   // Finally, order by inscriptionDate, ascending

        if ($championshiplist !== null) {
            // Comparer la relation (championshipList) de l'équipe avec le championnat donné
            $queryBuilder->andWhere(':championshiplist = t.championshipList')
                         ->setParameter('championshiplist', $championshiplist);
        }

        return $queryBuilder->getQuery()->getResult();
    }

//    /**
//     * @return Team[] Returns an array of Team objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
