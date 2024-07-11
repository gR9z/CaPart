<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
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

    public function findByCriteria(array $criteria, ?User $user): array
    {
        $qb = $this->createQueryBuilder('e');

        $conditions = $qb->expr()->andX();

        if (!empty($criteria['name'])) {
            $conditions->add($qb->expr()->like('e.name', ':name'));
            $qb->setParameter('name', '%' . $criteria['name'] . '%');
        }

        if (!empty($criteria['startDate'])) {
            $conditions->add($qb->expr()->gte('e.startDateTime', ':startDate'));
            $qb->setParameter('startDate', $criteria['startDate']->format('Y-m-d'));
        }

        if (!empty($criteria['endDate'])) {
            $conditions->add($qb->expr()->lte('e.startDateTime', ':endDate'));
            $qb->setParameter('endDate', $criteria['endDate']->format('Y-m-d'));
        }

        if (!empty($criteria['location'])) {
            $conditions->add($qb->expr()->eq('e.location', ':location'));
            $qb->setParameter('location', $criteria['location']);
        }

        if (!empty($criteria['filters'])) {
            foreach ($criteria['filters'] as $filter) {
                switch ($filter) {
                    case 'pastEvents':
                        $conditions->add($qb->expr()->lt('e.startDateTime', ':now'));
                        $qb->setParameter('now', new \DateTime());
                        break;
                    case 'myEvents':
                        if ($user) {
                            $conditions->add($qb->expr()->isMemberOf(':user', 'e.participants'));
                            $qb->setParameter('user', $user);
                        }
                        break;
                    case 'notMyEvents':
                        if ($user) {
                            $subQb = $this->createQueryBuilder('sub')
                                ->select('sub.id')
                                ->innerJoin('sub.participants', 'sp')
                                ->andWhere('sp.id = :userId');
                            $conditions->add($qb->expr()->notIn('e.id', $subQb->getDQL()));
                            $qb->setParameter('userId', $user->getId());
                        }
                        break;
                    case 'organizedEvents':
                        if ($user) {
                            $conditions->add($qb->expr()->eq('e.organizer', ':organizerId'));
                            $qb->setParameter('organizerId', $user->getId());
                        }
                        break;
                }
            }
        }

        $qb->andWhere($conditions);

        return $qb->getQuery()->getResult();
    }

}
