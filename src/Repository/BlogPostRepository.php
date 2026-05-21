<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    /**
     * Find all published posts ordered by creation date (newest first).
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('bp')
            ->orderBy('bp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all posts (published and unpublished, for admin) ordered by creation date (newest first).
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('bp')
            ->orderBy('bp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
