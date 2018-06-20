<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Match;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Match|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match[]    findAll()
 * @method Match[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Match::class);
    }

    /**
     * @param string $homeTeamCzechName
     * @param string $awayTeamCzechName
     * @param Match[]|null $allStoredMatches
     * @return Match|null
     */
    public function findMatchByTeamsCzechName(string $homeTeamCzechName, string $awayTeamCzechName, array $allStoredMatches = null): ?Match
    {
        if ($allStoredMatches) {
            foreach ($allStoredMatches as $storedMatch) {
                if (mb_strtolower($storedMatch->getHomeTeam()->getCzechName()) === $homeTeamCzechName &&
                    mb_strtolower($storedMatch->getAwayTeam()->getCzechName()) === $awayTeamCzechName) {
                    return $storedMatch;
                }
            }
        }
        return null;
    }

    /**
     * @param Team $homeTeam
     * @param Team $awayTeam
     * @param Match[]|null $allStoredMatches
     * @return Match|null
     */
    public function findMatchByTeams(Team $homeTeam, Team $awayTeam, array $allStoredMatches = null): ?Match
    {
        if ($allStoredMatches) {
            foreach ($allStoredMatches as $storedMatch) {
                if ($storedMatch->getHomeTeam() === $homeTeam &&
                    $storedMatch->getAwayTeam() === $awayTeam) {
                    return $storedMatch;
                }
            }
        }
        return null;
    }

    /**
     * @param Group|null $group
     * @return Match[]
     */
    public function getFinishedMatches(Group $group = null): array
    {
        $qb = $this->createQueryBuilder('match')
            ->addSelect('homeTeam')
            ->addSelect('awayTeam')
            ->addSelect('tips')
            ->addSelect('point')
            ->andWhere('match.finished = true')
            ->leftJoin('match.homeTeam', 'homeTeam')
            ->leftJoin('match.awayTeam', 'awayTeam')
            ->leftJoin('match.tips', 'tips')
            ->leftJoin('tips.point', 'point')
            ->orderBy('match.date', 'DESC');

        if ($group) {
            $qb->andWhere('match.group = :group')
               ->setParameter('group', $group);
        }

        return $qb->getQuery()
        ->getResult();
    }

    /**
     * @return Match[]
     */
    public function getFinishedAndUnprocessedMatches(): array
    {
        $qb = $this->createQueryBuilder('match')
            ->addSelect('tips')
            ->andWhere('match.finished = true')
            ->andWhere('match.tipsProcessed = false')
            ->leftJoin('match.tips', 'tips');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param Group|null $group
     * @return Match[]
     */
    public function getFutureMatches(Group $group = null): array
    {
        $qb = $this->createQueryBuilder('match')
            ->addSelect('homeTeam')
            ->addSelect('awayTeam')
            ->addSelect('tips')
            ->addSelect('point')
            ->andWhere('match.finished = false')
            ->leftJoin('match.homeTeam', 'homeTeam')
            ->leftJoin('match.awayTeam', 'awayTeam')
            ->leftJoin('match.tips', 'tips')
            ->leftJoin('tips.point', 'point')
            ->orderBy('match.date', 'ASC');

        if ($group) {
            $qb->andWhere('match.group = :group')
                ->setParameter('group', $group);
        }

        return $qb->getQuery()
            ->getResult();
    }

//    /**
//     * @return Match[] Returns an array of Match objects
//     */
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
    public function findOneBySomeField($value): ?Match
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
