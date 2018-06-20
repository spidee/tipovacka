<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param string $fifaCode
     * @param Team[]|null $allStoredTeams
     * @return Team|null
     */
    public function findTeamByFifaCode(string $fifaCode, array $allStoredTeams = null): ?Team
    {
        if ($allStoredTeams) {
            foreach ($allStoredTeams as $storedTeam) {
                if ($storedTeam->getFifaCode() === $fifaCode) {
                    return $storedTeam;
                }
            }
        }
        return null;
    }

    /**
     * @param int $id
     * @param Team[]|null $allStoredTeams
     * @return Team|null
     */
    public function findTeamById(int $id, array $allStoredTeams = null): ?Team
    {
        if ($allStoredTeams) {
            foreach ($allStoredTeams as $storedTeam) {
                if ($storedTeam->getId() === $id) {
                    return $storedTeam;
                }
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @param Team[]|null $allStoredTeams
     * @return Team|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTeamByCzechName(string $name, array $allStoredTeams = null): ?Team
    {
        if ($allStoredTeams) {
            foreach ($allStoredTeams as $storedTeam) {
                if (mb_strtolower($storedTeam->getCzechName()) === mb_strtolower($name)) {
                    return $storedTeam;
                }
            }
        }
        else {
            return $this->createQueryBuilder('team')
                ->andWhere('team.czechName = :name')
                ->setParameter('name', $name)
                ->orderBy('t.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        }
        return null;
    }


//    /**
//     * @return Team[] Returns an array of Team objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
