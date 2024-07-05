<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/events', name: 'events_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/eventsList.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/{id}', name: 'details', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found' . $event);
        }

        if (!$this->isGranted('ROLE_ADMIN') && $event !== $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        return $this->render('event/eventDetails.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        EventService $eventService
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('The user is not an instance of the expected User class.');
        }

        $result = $eventService->createEvent($user, $request);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
            return $this->redirectToRoute('events_list');
        } else {
            if (isset($result['message'])) {
                $this->addFlash('error', $result['message']);
            }
        }

        return $this->render('event/eventCreate.html.twig', ['form' => $result['form']->createView()]);
    }

    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function updateEvent(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Event updated');

            return $this->redirectToRoute('events_list');
        }

        return $this->render('event/eventUpdate.html.twig', [
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