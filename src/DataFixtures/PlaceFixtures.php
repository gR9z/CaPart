<?php

namespace App\DataFixtures;

use App\Entity\Place;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en_US');

        $cityReferences = [];
        for ($i = 0; $i < 20; $i++) {
            $cityReferences[] = $this->getReference('city_' . $i);
        }

        $places = [];

        for ($i = 0; $i < 100; $i++) {
            $place = new Place();
            $place->setName($faker->company);
            $place->setStreet($faker->streetAddress);
            $place->setLongitude($faker->longitude);
            $place->setLatitude($faker->latitude);
            $place->setCity($faker->randomElement($cityReferences));

            $manager->persist($place);
            $places[] = $place;
        }

        $manager->flush();

        foreach ($places as $i => $place) {
            $this->addReference('place_' . $i, $place);
        }
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
