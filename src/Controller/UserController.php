<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserSearchType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user_list', methods: ['GET', 'POST'])]
    public function list(UserRepository $userRepository, Request $request): Response
    {
        $searchForm = $this->createForm(UserSearchType::class);
        $searchForm->handleRequest($request);

        try {
            if($searchForm->isSubmitted() && $searchForm->isValid()) {
                $criteria = $searchForm->getData();
                $users = $userRepository->findByLastName($criteria['lastName']);
            } else {
                $users = $userRepository->findAll();
            };
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            $users = [];
        }

        return $this->render('user/usersList.html.twig', [
            'users' => $users,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/user/{id}', name: 'user_details', methods: ['GET'])]
    public function show(UserRepository $userRepository,Security $security, ?int $id = null): Response
    {
        $loggedInUser = $security->getUser();

        if($id !== null) {
            $user = $userRepository->find($id);
        } else {
            $user = $loggedInUser;
        }

        if($user) {
            return $this->render('user/userDetails.html.twig', [
                'user' => $user,
            ]);
        }

        throw $this->createNotFoundException('User not found');
    }

    #[Route('/user/update/{id}', name: 'user_update', methods: ['GET', 'POST'])]
    public function updateUser(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($id);
            if (!$user) {
                throw new EntityNotFoundException('User not found');
            }

            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', "User updated");

                return $this->redirectToRoute('user_list');
            }

            return $this->render('user/userUpdate.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('user_list');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            return $this->redirectToRoute('user_list');
        }
    }

    #[Route('/user/delete/{id}', name: "user_delete", requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        try {
            $user = $userRepository->find($id);

            if(!$user) {
                throw new \Exception('User not found');
            }

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted successfully');

        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());

        }

        return $this->redirectToRoute("user_list");
    }

}

