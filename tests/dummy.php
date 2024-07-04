<?php


namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function test_getWishCreateWithoutLoggedInUser(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'wish/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertSelectorTextContains('h2', 'Please sign in');
    }

    public function test_getWishCreateWithLoggedInUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin@admin.fr']);
        $client->loginUser($user);

        $crawler = $client->request('GET', 'wish/create');
        $this->assertSelectorTextContains('h2', 'Create a new wish');

        $client->submitForm("Add Wish", [
            'wish[title]' => "Ca part",
            'wish[description]' => "Ca part en description",
            'wish[categories]' => "3"
        ]);

        $client->followRedirect();
        $this->assertRouteSame("wish_detail");

    }
}