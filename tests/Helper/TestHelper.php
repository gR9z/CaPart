<?php

namespace App\Tests\Helper;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Place;
use App\Entity\State;
use App\Entity\User;
use App\Enum\StateLabel;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class TestHelper
{
    private EntityManagerInterface $entityManager;
    private \Faker\Generator $faker;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Factory::create('en_US');
    }

    public function createAllStates(): array
    {
        $states = [];

        foreach (StateLabel::cases() as $stateLabel) {
            $state = new State();
            $state->setLabel($stateLabel);

            $this->entityManager->persist($state);
            $states[] = $state;
        }

        $this->entityManager->flush();

        return $states;
    }

    public function createLocation(): Location
    {
        $location = new Location();
        $location->setName($this->faker->unique()->company());

        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $location;
    }

    public function createCity(): City
    {
        $city = new City();
        $city->setName($this->faker->unique()->city());
        $city->setZipCode($this->faker->postcode());

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    public function createPlace(City $city): Place
    {
        $place = new Place();
        $place->setName($this->faker->company());
        $place->setStreet($this->faker->streetAddress());
        $place->setLongitude($this->faker->longitude());
        $place->setLatitude($this->faker->latitude());
        $place->setCity($city);

        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return $place;
    }

    public function createAdminUser(Location $location): User
    {
        $user = new User();
        $user->setUsername($this->faker->unique()->userName());
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $user->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym'); // Using a hashed password
        $user->setEmail($this->faker->unique()->email());
        $user->setPhoneNumber($this->faker->phoneNumber);
        $user->setLocation($location);
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setActive(true);
        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function createUser(Location $location): User
    {
        $user = new User();
        $user->setUsername($this->faker->unique()->userName());
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $user->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym'); // Using a hashed password
        $user->setEmail($this->faker->unique()->email());
        $user->setPhoneNumber($this->faker->phoneNumber());
        $user->setLocation($location);
        $user->setRoles(["ROLE_USER"]);
        $user->setActive(true);
        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function createEvent(User $organizer, Location $location, State $state, Place $place): Event
    {
        $event = new Event();
        $event->setOrganizer($organizer);
        $event->setLocation($location);
        $event->setEventDetails($this->faker->text(500));
        $event->setState($state);
        $event->setName($this->faker->sentence(4));
        $event->setDuration($this->faker->numberBetween(60, 480));
        $event->setMaxRegistrations($this->faker->numberBetween(4, 50));
        $event->setPlace($place);

        $registrationDeadline = $this->faker->dateTimeBetween('+5 days', '+2 months');
        $event->setRegistrationDeadline($registrationDeadline);

        $startDateTime = (clone $registrationDeadline)->modify('+1 day');
        $event->setStartDateTime($startDateTime);

        $event->addParticipant($organizer);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

}