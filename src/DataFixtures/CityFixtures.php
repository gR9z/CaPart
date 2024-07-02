<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en_US');

        $cities = [];

        for ($i = 0; $i < 20; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setZipCode($faker->postcode);
            $manager->persist($city);
            $cities[] = $city;
        }

        $manager->flush();

        foreach ($cities as $i => $city) {
            $this->addReference('city_' . $i, $city);
        }
    }
}

