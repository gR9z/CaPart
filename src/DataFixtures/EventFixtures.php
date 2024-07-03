<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en_US');

        $userReferences = [];
        for ($i = 0; $i < 300; $i++) {
            $userReferences[] = $this->getReference('user_' . $i);
        }

        $stateReferences = [];
        for ($i = 0; $i < 6; $i++) {
            $stateReferences[] = $this->getReference('state_' . $i);
        }

        $placeReferences = [];
        for ($i = 0; $i < 100; $i++) {
            $placeReferences[] = $this->getReference('place_' . $i);
        }

        $locationReferences = [];
        for ($i = 0; $i < 5; $i++) {
            $locationReferences[] = $this->getReference('campus_' . $i);
        }

        $events = [];
        for ($i = 0; $i < 30; $i++) {
            $event = new Event();
            $event->setOrganizer($faker->randomElement($userReferences));
            $event->setLocation($faker->randomElement($locationReferences));
            $event->setEventDetails($faker->text(500));
            $event->setState($faker->randomElement($stateReferences));
            $event->setName($faker->sentence(4));
            $event->setDuration($faker->numberBetween(60, 480));
            $event->setMaxRegistrations($faker->numberBetween(4, 50));
            $event->setPlace($faker->randomElement($placeReferences));

            $registrationDeadline = $faker->dateTimeBetween('+5 days', '+2 months');
            $event->setRegistrationDeadline($registrationDeadline);

            $startDateTime = (clone $registrationDeadline)->modify('+1 day');
            $event->setStartDateTime($startDateTime);

            $numParticipants = $faker->numberBetween(4, 50);
            $participants = $faker->randomElements($userReferences, $numParticipants);

            foreach ($participants as $participant) {
                $event->addParticipant($participant);
            }

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            StateFixtures::class,
            PlaceFixtures::class,
            LocationFixtures::class,
        ];
    }
}
