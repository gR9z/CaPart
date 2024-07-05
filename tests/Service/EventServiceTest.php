<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use App\Enum\StateLabel;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EventService $eventService;
    private EntityRepository $stateRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stateRepository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [State::class, $this->stateRepository],
            ]);

        $this->eventService = new EventService($this->entityManager);
    }

    public function testCreateEventSuccess()
    {
        $user = new User();
        $user->setLocation(new Location());

        $event = new Event();
        $state = new State();
        $state->setLabel(StateLabel::Created);

        $this->stateRepository->method('findOneBy')
            ->with(['label' => StateLabel::Created])
            ->willReturn($state);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Event::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->eventService->createEvent($user, $event);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event created!', $result['message']);
        $this->assertInstanceOf(Event::class, $result['event']);
    }

    public function testCreateEventStateNotFound()
    {
        $user = new User();
        $user->setLocation(new Location());

        $event = new Event();

        $this->stateRepository->method('findOneBy')
            ->with(['label' => StateLabel::Created])
            ->willReturn(null);

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $result = $this->eventService->createEvent($user, $event);

        $this->assertFalse($result['success']);
        $this->assertEquals('The default state "created" was not found in the database.', $result['message']);
    }
}
