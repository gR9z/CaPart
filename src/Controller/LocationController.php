<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationSearchType;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    #[Route('/locations', name: 'locations_list', methods: ['GET', 'POST'])]
    public function locationsList(LocationRepository $locationRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $searchForm = $this->createForm(LocationSearchType::class);
        $searchForm->handleRequest($request);

        try {
            if($searchForm-> isSubmitted() && $searchForm->isValid()) {
                $criteria = $searchForm->getData();
                $locations = $locationRepository->findByName($criteria['name']);
            } else {
                $locations = $locationRepository->findAll();
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            $locations = [];
        }


        return $this->render('location/locationsList.html.twig', [
            'locations' => $locations,
            'searchForm' => $searchForm->createView(),
        ]);
    }
    
    #[Route('/locations/create', name: 'location_create', methods: ['GET', 'POST'])]
    public function createLocation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        try {
            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($location);
                $entityManager->flush();

                $this->addFlash('success', "Location created successfully");

                return $this->redirectToRoute('locations_list');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
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

        try {
            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Location updated');

                return $this->redirectToRoute('locations_list');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->render('location/locationUpdate.html.twig', [
            'form' => $form->createView(),
            'location' => $location,
        ]);
    }

    #[Route('/locations/delete/{id}', name: 'location_delete', methods: ['GET'])]
    public function deleteLocation(LocationRepository $locationRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        try {
            $location = $locationRepository->find($id);

            if(!$location) {
                throw new \Exception('Location not found');
            }

            $entityManager->remove($location);
            $entityManager->flush();

            $this->addFlash('success', 'Location deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute('locations_list');
    }
}