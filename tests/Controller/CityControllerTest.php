<?php

namespace App\Tests\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CityControllerTest extends WebTestCase
{
    public function testUpdateCity(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $city = new City();
        $city->setName('Maccrage');
        $city->setZipCode('54321');
        $entityManager->persist($city);
        $entityManager->flush();

        $cityId = $city->getId();

        $crawler = $client->request('GET', '/cities/' . $cityId . '/update');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update')->form([
            'city[name]' => 'Baal',
            'city[zipCode]' => '98765',
        ]);

        $client->submit($form);

        $client->followRedirect();

        $this->assertSelectorTextContains('table', "Baal");

        $cityRepository = static::getContainer()->get(CityRepository::class);
        $updatedCity = $cityRepository->find($cityId);
        $this->assertNotNull($updatedCity);
        $this->assertEquals('Baal', $updatedCity->getName());
        $this->assertEquals('98765', $updatedCity->getZipCode());
    }
}
