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

        $cityReferences = [];
        for($i = 0; $i < 20; $i++) {
            $cityReferences[] = $this->getReference('city_'.$i);
        }

        $adminUser = new User();
        $adminUser->setUsername("fuckingAdmin");
        $adminUser->setFirstName("Jean-Baptiste");
        $adminUser->setLastName("Poquelin");
        $adminUser->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym');
        $adminUser->setEmail('admin@capart.fr');
        $adminUser->setPhoneNumber($faker->phoneNumber);
        $adminUser->setCity($faker->randomElement($cityReferences));
        $adminUser->setRoles($roles);
        $adminUser->setActive(true);
        $adminUser->setVerified(true);

        $simpleUser = new User();
        $simpleUser->setUsername("fuckingUser");
        $simpleUser->setFirstName("Quentin");
        $simpleUser->setLastName("Tarantino");
        $simpleUser->setPassword('$2y$13$aZLqx0rob5TAJIb2NMsgCOcy1V7Aq8KYfqExsD0FcfOvopAJOBQym');
        $simpleUser->setEmail('user@capart.fr');
        $simpleUser->setPhoneNumber($faker->phoneNumber);
        $simpleUser->setCity($faker->randomElement($cityReferences));
        $simpleUser->setRoles([$roles[0]]);
        $simpleUser->setActive(true);
        $simpleUser->setVerified(true);

        $users = [];
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
            $users[] = $user;
        }

        $manager->persist($adminUser);
        $manager->persist($simpleUser);

        $manager->flush();

        $this->addReference('admin_user', $adminUser);
        $this->addReference('simple_user', $simpleUser);

        foreach ($users as $i => $user) {
            $this->addReference('user_' . $i, $user);
        }
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
