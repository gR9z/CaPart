<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/usersList.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}', name: 'user_details', methods: ['GET'])]
    public function show(UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);

        return $this->render('user/userDetails.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/update', name: 'user_update', methods: ['GET', 'POST'])]
    public function updateUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', "User updated");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/userUpdate.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}

