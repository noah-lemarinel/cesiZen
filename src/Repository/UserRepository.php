<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function searchUsers(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) LIKE LOWER(:query)')
            ->orWhere('LOWER(u.name) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
