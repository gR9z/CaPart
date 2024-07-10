<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Form\EventSearchType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/events', name: 'events_')]
class EventController extends AbstractController
{

    private EventService $eventService;
    private UserService $userService;
    private SluggerInterface $slugger;

    public function __construct(EventService $eventService, UserService $userService, SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
        $this->userService = $userService;
        $this->eventService = $eventService;
    }

    #[Route('/', name: 'list', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function list(EventRepository $eventRepository, Request $request): Response
    {
        $searchForm = $this->createForm(EventSearchType::class);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()) {
            $criteria = $searchForm->getData();
            $events = $eventRepository->findByName($criteria['name']);
        } else {
            $events = $eventRepository->findAll();
        }

        foreach ($events as $event) {
            $this->eventService->updateEventState($event);
        }

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/view/{slug}', name: 'details', requirements: ['slug' => "[a-z0-9-]+"], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(EventRepository $eventRepository, Request $request): Response
    {
        $id = $request->query->get('id');

        if (!$id) {
            throw $this->createNotFoundException('ID parameter is missing');
        }

        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found: ' . $event);
        }

        $this->eventService->updateEventState($event);

        return $this->render('event/details.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EventService $eventService): Response
    {
        $user = $this->userService->getAuthenticatedUser();

        $event = new Event();
        $form = $this->createForm(EventType::class, $event, [
            'location' => $user->getLocation()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $eventService->createEvent($user, $event);

            if ($result['success']) {
                $this->addFlash('success', $result['message']);
                return $this->redirectToRoute('events_list');
            } else {
                $this->addFlash('error', $result['message']);
            }
        }

        return $this->render('event/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function updateEvent(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Event updated');

            return $this->redirectToRoute('events_list');
        }

        return $this->render('event/update.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['GET'])]
    public function deleteEvent(EventRepository $eventRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $event = $eventRepository->find($id);

        $entityManager->remove($event);
        $entityManager->flush();
        return $this->redirectToRoute('events_list');
    }

    #[Route('/publish/{id}', name: 'publish', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function publish(EventRepository $eventRepository, int $id): Response {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found: ' . $event);
        }

        $result = $this->eventService->publishEvent($event);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('events_details', ['slug' => $this->slugger->slug(strtolower($event->getName())), 'id' => $event->getId()]);
    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(EventRepository $eventRepository, int $id): Response {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found: ' . $event);
        }

        $result = $this->eventService->cancelEvent($event);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('events_details', ['slug' => $this->slugger->slug(strtolower($event->getName())), 'id' => $event->getId()]);
    }
    #[Route('/register/{id}', name: 'register', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function register(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $result = $this->eventService->registerUserToEvent($event);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('events_details', ['slug' => $this->slugger->slug(strtolower($event->getName())), 'id' => $event->getId()]);
    }

    #[Route('/unregister/{id}', name: 'unregister', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function unregister(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $result = $this->eventService->unregisterUserFromEvent($event);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('events_details', ['slug' => $this->slugger->slug(strtolower($event->getName())), 'id' => $event->getId()]);
    }

    #[Route('/places', name: 'get_places_by_city', methods: ['GET'])]
    public function getPlacesByCity(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $cityId = $request->query->get('cityId');

        if (!$cityId) {
            return new JsonResponse(['error' => 'City ID is required'], 400);
        }

        $city = $em->getRepository(City::class)->find($cityId);

        if (!$city) {
            return new JsonResponse(['error' => 'City not found'], 404);
        }

        $places = $city->getPlaces();
        $responseArray = [];

        foreach ($places as $place) {
            $responseArray[] = [
                'id' => $place->getId(),
                'name' => $place->getName(),
            ];
        }

        return new JsonResponse($responseArray);
    }

    #[Route('/place-details', name: 'get_place_details', methods: ['GET'])]
    public function getPlaceDetails(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $placeId = $request->query->get('placeId');

        if (!$placeId) {
            return new JsonResponse(['error' => 'Place ID is required'], 400);
        }

        $place = $em->getRepository(Place::class)->find($placeId);

        if (!$place) {
            return new JsonResponse(['error' => 'Place not found'], 404);
        }

        $responseArray = [
            'street' => $place->getStreet(),
            'longitude' => $place->getLongitude(),
            'latitude' => $place->getLatitude(),
        ];

        return new JsonResponse($responseArray);
    }
}