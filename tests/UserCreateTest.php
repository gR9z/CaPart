<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserCreateTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();
    }

    public function testCreateUser(): void {
        $crawler = $this->client->request('GET', '/user/create');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();
        $form['registration_form[username]'] = 'Ultra';
        $form['registration_form[firstName]'] = 'Roboute';
        $form['registration_form[lastName]'] = 'Guilliman';
        $form['registration_form[phoneNumber]'] = '123456789';
        $form['registration_form[email]'] = 'guilliman@maccrage.um';
        $form['registration_form[plainPassword][first]'] = 'maccrage';
        $form['registration_form[plainPassword][second]'] = 'maccrage';
        $form['registration_form[agreeTerms]'] = 'maccrage';

        $this->client->submit($form);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'User created');

        $user = $this->userRepository->findOneByEmail('guilliman@maccrage.um');
        $this->assertNotNull($user, 'L\'utilisateur devrait être trouvé en base de données.');

        // Vérifier les détails de l'utilisateur
        $this->assertEquals('Ultra', $user->getUsername(), 'Le nom d\'utilisateur devrait être "Ultra".');
        $this->assertEquals('Roboute', $user->getFirstName(), 'Le prénom devrait être "Roboute".');
        $this->assertEquals('Guilliman', $user->getLastName(), 'Le nom de famille devrait être "Guilliman".');
        $this->assertEquals('123456789', $user->getPhoneNumber(), 'Le numéro de téléphone devrait être "123456789".');

    }
}
