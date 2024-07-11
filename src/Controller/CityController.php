<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CitySearchType;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CityController extends AbstractController
{
    #[Route('/cities', name:'cities_list', methods: ['GET', 'POST'])]
    public function citiesList(CityRepository $cityRepository, Request $request): Response
    {
        $searchForm = $this->createForm(CitySearchType::class);
        $searchForm->handleRequest($request);

        try {
            if($searchForm->isSubmitted() && $searchForm->isValid()) {
                $criteria = $searchForm->getData();
                $cities = $cityRepository->findByName($criteria['name']);
            } else {
                $cities = $cityRepository->findAll();
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occured ' . $e->getMessage());
            $cities = [];
        }

        return $this->render('city/citiesList.html.twig', [
            'cities' => $cities,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/cities/create', name: 'city_create', methods: ['GET', 'POST'])]
    public function createCity(Request $request, EntityManagerInterface $entityManager): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        try {
            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($city);
                $entityManager->flush();

                $this->addFlash('success', "City created successfully");

                return $this->redirectToRoute('cities_list');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occured ' . $e->getMessage());
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

        try {
            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'City updated');

                return $this->redirectToRoute('cities_list');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->render('city/cityUpdate.html.twig', [
            'form' => $form->createView(),
            'city' => $city,
        ]);
    }

    #[Route('/cities/delete/{id}', name: "city_delete", requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteCity(CityRepository $cityRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        try {
            $city = $cityRepository->find($id);

            if(!$city) {
                throw new \Exception('City not found');
            }

            $entityManager->remove($city);
            $entityManager->flush();

            $this->addFlash('success', 'City deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute("cities_list");
    }

}