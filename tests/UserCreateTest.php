<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserCreateTest extends WebTestCase
{
    public function test_createUserBySignUp(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'register');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Register')->form([
            'registration_form[username]' => 'testuser',
            'registration_form[firstName]' => 'Test',
            'registration_form[lastName]' => 'User',
            'registration_form[phoneNumber]' => '123456789',
            'registration_form[email]' => 'testuser@example.com',
            'registration_form[plainPassword][first]' => 'testpassword',
            'registration_form[plainPassword][second]' => 'testpassword',
            'registration_form[location]' => '',
            'registration_form[agreeTerms]' => true,
        ]);

        $client->submit($form);

        $client->followRedirect();

        $this->assertResponseIsSuccessful();

    }


}