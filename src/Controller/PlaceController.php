<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    #[Route('/places', name: 'places_list', methods: ['GET'])]
    public function placesList(PlaceRepository $placeRepository): Response
    {
        $places = $placeRepository->findAll();

        return $this->render('place/placesList.html.twig', [
            'places' => $places,
        ]);
    }

    #[Route('/places/create', name: 'place_create', methods: ['GET', 'POST'])]
    public function createPlace(Request $request, EntityManagerInterface $entityManager): Response
    {
        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($place);
            $entityManager->flush();

            $this->addFlash('success', "Place created successfully");

            return $this->redirectToRoute('places_list');
        }

        return $this->render('place/placeCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/places/update', name: 'place_update', methods: ['GET', 'POST'])]
    public function updatePlace(Request $request, Place $place, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Place updated');

            return $this->redirectToRoute('places_list');
        }

        return $this->render('place/placeUpdate.html.twig', [
            'form' => $form->createView(),
            'place' => $place,
        ]);
    }

    #[Route('/places/delete/{id}', name: 'place_delete', methods: ['GET'])]
    public function deletePlace(PlaceRepository $placeRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $place = $placeRepository->find($id);
        $entityManager->remove($place);
        $entityManager->flush();
        return $this->redirectToRoute('places_list');
    }
}