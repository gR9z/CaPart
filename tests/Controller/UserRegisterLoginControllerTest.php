<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;

class UserRegisterLoginControllerTest extends WebTestCase
{

    public function testRegister()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        $messageLogger = new MessageLoggerListener();
        $container->get('event_dispatcher')->addSubscriber($messageLogger);

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('ça démarre !')->form([
            'registration_form[username]' => 'testuser',
            'registration_form[firstName]' => 'Test',
            'registration_form[lastName]' => 'User',
            'registration_form[phoneNumber]' => '123456789',
            'registration_form[email]' => 'testuser@example.com',
            'registration_form[plainPassword][first]' => 'testpassword',
            'registration_form[plainPassword][second]' => 'testpassword',
            'registration_form[agreeTerms]' => "on",
        ]);

        $client->submit($form);

//        $this->assertTrue($client->getResponse()->isRedirection());
        $client->followRedirect();

        $user = $userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->assertNotNull($user);
        $this->assertTrue($user->isActive());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

//        $messages = $messageLogger->getEvents()->getMessages();
//        $this->assertCount(1, $messages, 'One email should have been sent.');
//
//        if (count($messages) > 0) {
//            /** @var Email $email */
//            $email = $messages[0];
//            $this->assertEquals('testuser@example.com', $email->getTo()[0]->getAddress(), 'Email should be sent to the correct address.');
//        }
    }

    public function testLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Login')->form();
        $form['email'] = 'admin@capart.fr';
        $form['password'] = 'password';
        $client->submit($form);

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorNotExists('.alert-danger'); // Check if no login error message is present
    }

    public function testUpdateUser()
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $user = new User();
        $user->setUsername('testuser_' . uniqid());
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPhoneNumber('123456789');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword('$2y$10$xQ9HiGEYrkFgit2Qr2qmR.mqGvCi.iBTOnWXkvaJSDBUSYy1eXdvi');
        $user->setActive(true);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        $userId = $user->getId();

        $crawler = $client->request('GET', '/user/' . $userId . '/update');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update')->form([
            'registration_form[username]' => 'updateduser',
            'registration_form[firstName]' => 'Updated',
            'registration_form[lastName]' => 'User',
            'registration_form[phoneNumber]' => '987654321',
            'registration_form[email]' => 'updateduser@example.com',
            'registration_form[plainPassword][first]' => 'newpassword',
            'registration_form[plainPassword][second]' => 'newpassword',
            'registration_form[agreeTerms]' => 1,
        ]);

        $client->submit($form);

        $client->followRedirect();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $updatedUser = $userRepository->find($userId);
        $this->assertNotNull($updatedUser);
        $this->assertEquals('updateduser', $updatedUser->getUsername());
        $this->assertEquals('Updated', $updatedUser->getFirstName());
        $this->assertEquals('User', $updatedUser->getLastName());
        $this->assertEquals('987654321', $updatedUser->getPhoneNumber());
        $this->assertEquals('updateduser@example.com', $updatedUser->getEmail());
        $this->assertNotNull($updatedUser->getPassword()); // TODO trouver un moyen de bien comparer les mots de passe
    }

    public function test_deleteUser()
    {
        $client = static ::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $user = new User();
        $user->setUsername('testuser_' . uniqid());
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPhoneNumber('123456789');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('testpassword', PASSWORD_BCRYPT));
        $user->setActive(true);

        $entityManager->persist($user);
        $entityManager->flush();

        $userId = $user->getId();

        $client->request('GET', '/user/delete/' . $userId);

//        $this->assertTrue($client->getResponse()->isRedirect('user/usersList.html.twig'));

        $client->followRedirect();

        $deletedUser = $entityManager->getRepository(User::class)->find($userId);
        $this->assertNull($deletedUser);
    }

    // En stand-by le temps qu'on configure l'envoi d'email
    public function testVerifyUserEmail()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $user = new User();
        $user->setUsername('testuser_' . uniqid());
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPhoneNumber('123456789');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('testpassword', PASSWORD_BCRYPT));
        $user->setActive(true);
        $user->setRoles(['ROLE_USER']);
        $user->setVerified(false);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $emailVerifier = $container->get(EmailVerifier::class);
        $emailVerifier->handleEmailConfirmation(
            new Request([], [], ['_route' => 'app_verify_email']), // Fake request
            $user
        );

        $entityManager->refresh($user);

        $this->assertTrue($user->isVerified());
    }
}