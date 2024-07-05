<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Enum\StateLabel;
use App\Service\EventService;
use App\Tests\Helper\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventControllerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private EventService $eventService;
    private TestHelper $testHelper;
    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->eventService = self::getContainer()->get(EventService::class);
        $this->testHelper = new TestHelper($this->entityManager);
        $this->faker = Factory::create('en_US');
    }

    public function testCreateEventAndCheckDatabase() {
        $states = $this->testHelper->createAllStates();
        $location = $this->testHelper->createLocation();
        $city = $this->testHelper->createCity();
        $place = $this->testHelper->createPlace($city);
        $user = $this->testHelper->createUser($location);

        $state = array_filter($states, fn($s) => $s->getLabel() === StateLabel::Created)[0];

        $event = new Event();
        $event->setOrganizer($user);
        $event->setLocation($location);
        $event->setEventDetails($this->faker->text(500));
        $event->setState($state);
        $event->setName($this->faker->sentence(4));
        $event->setDuration($this->faker->numberBetween(60, 480));
        $event->setMaxRegistrations($this->faker->numberBetween(4, 50));
        $event->setPlace($place);

        $registrationDeadline = $this->faker->dateTimeBetween('+5 days', '+2 months');
        $event->setRegistrationDeadline($registrationDeadline);

        $startDateTime = (clone $registrationDeadline)->modify('+1 day');
        $event->setStartDateTime($startDateTime);

        $event->addParticipant($user);

        $result = $this->eventService->createEvent($user, $event);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event created!', $result['message']);
        $this->assertInstanceOf(Event::class, $result['event']);

        $eventId = $result['event']->getId();
        $savedEvent = $this->entityManager->getRepository(Event::class)->find($eventId);

        $this->assertNotNull($savedEvent);
        $this->assertEquals($eventId, $savedEvent->getId());
    }
}
