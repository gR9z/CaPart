<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\State;
use App\Entity\User;
use App\Enum\StateLabel;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/events', name: 'events_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }


    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('The user is not an instance of the expected User class.');
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stateCreated = $entityManager->getRepository(State::class)->findOneBy(['label' => StateLabel::Created]);

            if ($stateCreated === null) {
                $this->addFlash('error', 'The default state "created" was not found in the database.');
                return $this->redirectToRoute('events_create');
            }

            $event->setState($stateCreated);
            $event->setLocation($user->getCity());
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created!');
            return $this->redirectToRoute('events_list');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
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