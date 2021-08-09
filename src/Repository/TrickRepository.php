<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    /**
     * Fonction permettant de charger le chargement des tricks
     * @param $trick
     * @param int $limit
     * @return int|mixed|string
     */
    public function loadTricks(int $limit = 6){
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }
}
