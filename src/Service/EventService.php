<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\State;
use App\Entity\User;
use App\Enum\StateLabel;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class EventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createEvent(User $user, Event $event): array
    {
        $location = $user->getLocation();

        $stateCreated = $this->entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Created]);

        if ($stateCreated === null) {
            return [
                'success' => false,
                'message' => 'The default state "created" was not found in the database.'
            ];
        }

        $event->setState($stateCreated);
        $event->setLocation($location);
        $event->setOrganizer($user);
        $event->addParticipant($user);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return [
            'success' => true,
            'event' => $event,
            'message' => 'Event created!'
        ];
    }
}