<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    #[Route('cities', name:'cities_list', methods: ['GET'])]
    public function citiesList(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();

        return $this->render('city/citiesList.html.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route('/cities/create', name: 'city_create', methods: ['GET', 'POST'])]
    public function createCity(Request $request, EntityManagerInterface $entityManager): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();

            $this->addFlash('success', 'City created successfully');

            return $this->redirectToRoute('cities_list');
        }

        return $this->render('city/cityCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('cities/{id}/update', name: 'city_update', methods: ['GET', 'POST'])]
    public function updateCity(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'City updated');

            return $this->redirectToRoute('cities_list');
        }

        return $this->render('city/cityUpdate.html.twig', [
            'form' => $form->createView(),
            'city' => $city,
        ]);
    }

    #[Route('/cities/delete/{id}', name: "city_delete", requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteCity(CityRepository $cityRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $city = $cityRepository->find($id);
        $entityManager->remove($city);
        $entityManager->flush();
        return $this->redirectToRoute("cities_list");
    }

}