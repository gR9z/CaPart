<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en_US');

        $roles = ["ROLE_USER", "ROLE_ADMIN"];

        $adminUser = new User();
        $adminUser->setVerified(true);
        $adminUser->setEmail('admin@admin.fr');
        $adminUser->setUsername('admin');
        $adminUser->setRoles($roles);
        $adminUser->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym');

        $simpleUser = new User();
        $simpleUser->setVerified(true);
        $simpleUser->setEmail('simple@user.fr');
        $simpleUser->setUsername('user');
        $simpleUser->setRoles([$roles[0]]);
        $simpleUser->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym');

        $cityReferences = [];
        for($i = 0; $i < 20; $i++) {
            $cityReferences[] = $this->getReference('city_'.$i);
        }

        for($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym');
            $user->setEmail($faker->unique()->email);
            $user->setPhoneNumber($faker->phoneNumber);
            $user->setCity($faker->randomElement($cityReferences));
            $user->setRoles($faker->randomElements($roles));
            $user->setActive(true);
            $user->setVerified($faker->boolean(50));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
