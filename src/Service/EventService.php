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
    private FormFactoryInterface $formFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface   $formFactory,
    )
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function createEvent(User $user, Request $request): array
    {
        $location = $user->getLocation();

        $event = new Event();
        $form = $this->formFactory->create(EventType::class, $event, [
            'location' => $location,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stateCreated = $this->entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Created]);

            if ($stateCreated === null) {
                return [
                    'success' => false,
                    'form' => $form,
                    'message' => 'The default state "created" was not found in the database.'
                ];
            }

            $event->setState($stateCreated);
            $event->setLocation($location);
            $event->setOrganizer($user);

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return [
                'success' => true,
                'event' => $event,
                'message' => 'Event created!'
            ];
        }

        return [
            'success' => false,
            'form' => $form
        ];
    }
}