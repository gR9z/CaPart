<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Login')->form();
        $form['email'] = 'admin@capart.fr';
        $form['password'] = 'password';
        $client->submit($form);

        // Step 3: Check if the login was successful
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorNotExists('.alert-danger'); // Check if no login error message is present
    }
}

