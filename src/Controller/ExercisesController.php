<?php

namespace App\Controller;

use App\Entity\BreathingExercise;
use App\Entity\User;
use App\Form\BreathingExerciseType;
use App\Repository\BreathingExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ExercisesController extends AbstractController
{
    #[Route('/exercises', name: 'exercises_index')]
    public function index(BreathingExerciseRepository $exerciseRepository, CacheInterface $cache): Response
    {
        $defaultExercises = $cache->get('breathing_exercises_default', function (ItemInterface $item) use ($exerciseRepository) {
            $item->expiresAfter(3600); // Cache for 1 hour

            return $exerciseRepository->findBy(['createdBy' => null]);
        });

        $userExercises = [];
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
            $userExercises = $cache->get('breathing_exercises_user_'.$userId, function (ItemInterface $item) use ($exerciseRepository) {
                $item->expiresAfter(3600); // Cache for 1 hour

                return $exerciseRepository->findBy(['createdBy' => $this->getUser()]);
            });
        }

        return $this->render('exercises/index.html.twig', [
            'exercises' => $defaultExercises,
            'userExercises' => $userExercises,
        ]);
    }

    #[Route('/exercises/{id<\d+>}', name: 'exercises_show')]
    public function show(BreathingExerciseRepository $exerciseRepository, int $id, CacheInterface $cache): Response
    {
        $exercise = $cache->get('breathing_exercise_'.$id, function (ItemInterface $item) use ($exerciseRepository, $id) {
            $item->expiresAfter(3600); // Cache for 1 hour

            return $exerciseRepository->find($id);
        });

        if (!$exercise) {
            throw $this->createNotFoundException('Exercice non trouvé.');
        }

        return $this->render('exercises/show.html.twig', [
            'exercise' => $exercise,
        ]);
    }

    #[Route('/exercises/create', name: 'exercises_create')]
    public function create(Request $request, EntityManagerInterface $em, CacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $exercise = new BreathingExercise();
        $form = $this->createForm(BreathingExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Only set createdBy for non-admin users
            // Admins create default exercises (createdBy stays null)
            if (!$this->isGranted('ROLE_ADMIN')) {
                $exercise->setCreatedBy($currentUser);
            }
            $em->persist($exercise);
            $em->flush();

            // Clear relevant cache
            $cache->delete('breathing_exercises_default');
            if ($currentUser) {
                $cache->delete('breathing_exercises_user_'.$currentUser->getId());
            }

            $this->addFlash('success', sprintf('Exercice "%s" créé avec succès!', $exercise->getName()));

            return $this->redirectToRoute('exercises_show', ['id' => $exercise->getId()]);
        }

        return $this->render('exercises/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/exercises/{id<\d+>}/edit', name: 'exercises_edit')]
    public function edit(Request $request, BreathingExercise $exercise, EntityManagerInterface $em, CacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Check if user is the creator or admin
        if ($exercise->getCreatedBy() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres exercices.');
        }

        $form = $this->createForm(BreathingExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Clear relevant cache
            $cache->delete('breathing_exercise_'.$exercise->getId());
            $cache->delete('breathing_exercises_default');
            if ($currentUser) {
                $cache->delete('breathing_exercises_user_'.$currentUser->getId());
            }

            $this->addFlash('success', sprintf('Exercice "%s" modifié avec succès!', $exercise->getName()));

            return $this->redirectToRoute('exercises_show', ['id' => $exercise->getId()]);
        }

        return $this->render('exercises/edit.html.twig', [
            'form' => $form->createView(),
            'exercise' => $exercise,
        ]);
    }

    #[Route('/exercises/{id<\d+>}/delete', name: 'exercises_delete', methods: ['POST'])]
    public function delete(Request $request, BreathingExercise $exercise, EntityManagerInterface $em, CacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Check if user is the creator or admin
        if ($exercise->getCreatedBy() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres exercices.');
        }

        if ($this->isCsrfTokenValid('delete'.$exercise->getId(), $request->request->get('_token'))) {
            $exerciseName = $exercise->getName();
            $em->remove($exercise);
            $em->flush();

            // Clear relevant cache
            $cache->delete('breathing_exercise_'.$exercise->getId());
            $cache->delete('breathing_exercises_default');
            if ($currentUser) {
                $cache->delete('breathing_exercises_user_'.$currentUser->getId());
            }

            $this->addFlash('success', sprintf('Exercice "%s" supprimé.', $exerciseName));
        }

        return $this->redirectToRoute('exercises_index');
    }
}
