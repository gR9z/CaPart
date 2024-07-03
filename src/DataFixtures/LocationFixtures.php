<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campusNames = ['ENI Quimper', 'ENI Nantes', 'ENI Rennes', 'ENI Niort', 'ENI Campus en ligne'];
        $campusReferences = [];

        foreach ($campusNames as $campusName) {
            $campus = new Location();
            $campus->setName($campusName);
            $manager->persist($campus);
            $campusReferences[] = $campus;
        }

        $manager->flush();

        foreach ($campusReferences as $index => $campusReference) {
            $this->addReference('campus_' . $index, $campusReference);
        }
    }
}
