<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findByCriteria(array $criteria): array
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($criteria['name'])) {
            $qb->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $criteria['name'] . '%');
        }

        if (!empty($criteria['startDate'])) {
            $qb->andWhere('e.startDateTime >= :startDate')
                ->setParameter('startDate', $criteria['startDate']->format('Y-m-d'));
        }

        if (!empty($criteria['endDate'])) {
            $qb->andWhere('e.startDateTime <= :endDate')
                ->setParameter('endDate', $criteria['endDate']->format('Y-m-d'));
        }
        if (!empty($criteria['location'])) {
            $qb->andWhere('e.location = :location')
                ->setParameter('location', $criteria['location']);
        }

        return $qb->getQuery()->getResult();
    }
}
