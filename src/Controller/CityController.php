<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}