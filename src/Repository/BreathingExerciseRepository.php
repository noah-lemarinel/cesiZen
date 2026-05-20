<?php

namespace App\Repository;

use App\Entity\BreathingExercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BreathingExercise>
 */
class BreathingExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreathingExercise::class);
    }

    public function findDefaultExercises(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.createdBy IS NULL')
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserExercises($user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
