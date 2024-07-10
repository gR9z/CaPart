<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\State;
use App\Entity\User;
use App\Enum\StateLabel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class EventService
{
    private EntityManagerInterface $entityManager;
    private UserService $userService;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
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

    public function updateEventState(Event $event): void
    {
        $currentDate = new \DateTime();

        static $statePast = null;
        if ($statePast === null) {
            $statePast = $this->entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Past->value]);
        }

        if ($statePast !== null && $event->getStartDateTime() < $currentDate && $event->getState()->getLabel() !== StateLabel::Past->value) {
            $event->setState($statePast);
            $this->entityManager->flush();
        }
    }

    public function publishEvent(Event $event): array
    {
        $user = $this->userService->getAuthenticatedUser();
        if ($user !== $event->getOrganizer()) {
            return [
                'success' => false,
                'event' => $event,
                'message' => 'You cannot publish this event!'
            ];
        }

        $stateOpen = $this->entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Open->value]);
        if ($stateOpen === null) {
            return [
                'success' => false,
                'message' => 'The state "open" was not found in the database.'
            ];
        }

        $event->setState($stateOpen);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Event published!'
        ];
    }

    public function cancelEvent(Event $event): array
    {
        $user = $this->userService->getAuthenticatedUser();
        if ($user !== $event->getOrganizer()) {
            return [
                'success' => false,
                'message' => 'You cannot cancel this event!'
            ];
        }

        $stateCancelled = $this->entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Cancelled->value]);
        if ($stateCancelled === null) {
            return [
                'success' => false,
                'message' => 'The state "cancelled" was not found in the database.'
            ];
        }

        $event->setState($stateCancelled);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Event cancelled!'
        ];
    }

    public function registerUserToEvent(Event $event): array
    {
        $user = $this->userService->getAuthenticatedUser();

        $validationResult = $this->validateRegistration($event, $user);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $event->addParticipant($user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'You have successfully registered for the event'
        ];
    }

    public function unregisterUserFromEvent(Event $event): array
    {
        $user = $this->userService->getAuthenticatedUser();

        if (!$event->getParticipants()->contains($user)) {
            return [
                'success' => false,
                'message' => 'You are not registered for this event'
            ];
        }

        $event->removeParticipant($user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'You have successfully unregistered from the event'
        ];
    }

    private function validateRegistration(Event $event, User $user): array
    {
        $checks = [
            $this->checkIfUserIsRegistered($event, $user),
            $this->checkRegistrationDeadline($event),
            $this->checkIfEventIsFull($event),
            $this->checkIfEventIsOpenOrOngoing($event)
        ];

        foreach ($checks as $check) {
            if (!$check['success']) {
                return $check;
            }
        }

        return ['success' => true];
    }

    private function checkIfUserIsRegistered(Event $event, User $user): array
    {
        if ($event->isUserRegistered($user)) {
            return [
                'success' => false,
                'message' => 'You are already registered for this event'
            ];
        }

        return ['success' => true];
    }

    private function checkRegistrationDeadline(Event $event): array
    {
        if (new \DateTime() > $event->getRegistrationDeadline()) {
            return [
                'success' => false,
                'message' => 'The registration deadline has passed'
            ];
        }

        return ['success' => true];
    }

    private function checkIfEventIsOpenOrOngoing(Event $event): array
    {
        if ($event->getState()->getLabel() !== 'open' && $event->getState()->getLabel() !== 'ongoing') {
            return [
                'success' => false,
                'message' => 'The event is not open for registration'
            ];
        }

        return ['success' => true];
    }

    private function checkIfEventIsFull(Event $event): array
    {
        if ($event->getParticipants()->count() >= $event->getMaxRegistrations()) {
            return [
                'success' => false,
                'message' => 'The event is full'
            ];
        }

        return ['success' => true];
    }
}