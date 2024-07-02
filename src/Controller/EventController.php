<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $form = $this->createForm(EventType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO SOMETHING
            $this->addFlash('success', 'Event created!');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}