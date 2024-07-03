<?php

namespace App\DataFixtures;

use App\Entity\State;
use App\Enum\StateLabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $states = [];

        foreach (StateLabel::cases() as $stateLabel) {
            $state = new State();
            $state->setLabel($stateLabel);
            $manager->persist($state);
            $states[] = $state;
        }

        $manager->flush();

        foreach ($states as $i => $state) {
            $this->addReference('state_' . $i, $state);
        }
    }
}
