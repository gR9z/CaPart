<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    #[Route('/locations', name: 'locations_list', methods: ['GET'])]
    public function locationsList(LocationRepository $locationRepository): Response
    {
        $locations = $locationRepository->findAll();

        return $this->render('location/locationsList.html.twig', [
            'locations' => $locations,
        ]);
    }

    #[Route('/locations/create', name: 'location_create', methods: ['GET', 'POST'])]
    public function createLocation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            $this->addFlash('success', 'Location created successfully');

            return $this->redirectToRoute('locations_list');
        }

        return $this->render('location/locationCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/locations/{id}/update', name: 'location_update', methods: ['GET', 'POST'])]
    public function updateLocation(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Location updated');

            return $this->redirectToRoute('locations_list');
        }

        return $this->render('locations/locationUpdate.html.twig', [
            'form' => $form->createView(),
            'location' => $location,
        ]);
    }

    public function deleteLocation(LocationRepository $locationRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $location = $locationRepository->find($id);
        $entityManager->remove($location);
        $entityManager->flush();
        return $this->redirectToRoute('locations_list');
    }
}