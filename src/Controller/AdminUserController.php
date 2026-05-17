<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminCreateUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users', name: 'admin_users_')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $query = $request->query->get('q', '');

        if ($query) {
            $users = $userRepository->searchUsers($query);
        } else {
            $users = $userRepository->findAll();
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'search_query' => $query,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(AdminCreateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email already exists
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cette adresse email existe déjà.');

                return $this->render('admin/users/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Get the plain password from form (password field is mapped: false)
            $plainPassword = $form->get('password')->getData() ?? '';

            // Hash the password
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            } else {
                $this->addFlash('error', 'Le mot de passe est requis.');

                return $this->render('admin/users/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', sprintf('Utilisateur "%s" créé avec succès.', $user->getEmail()));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', methods: ['POST'])]
    public function deactivate(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('deactivate_user', $token)) {
            throw $this->createAccessDeniedException();
        }

        // Prevent deactivating self
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas désactiver votre propre compte.');

            return $this->redirectToRoute('admin_users_index');
        }

        $user->setIsActive(false);
        $em->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été désactivé.', $user->getEmail()));

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/activate', name: 'activate', methods: ['POST'])]
    public function activate(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('activate_user', $token)) {
            throw $this->createAccessDeniedException();
        }

        $user->setIsActive(true);
        $em->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été réactivé.', $user->getEmail()));

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_user', $token)) {
            throw $this->createAccessDeniedException();
        }

        // Prevent deleting self
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');

            return $this->redirectToRoute('admin_users_index');
        }

        $userEmail = $user->getEmail();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été supprimé.', $userEmail));

        return $this->redirectToRoute('admin_users_index');
    }
}
