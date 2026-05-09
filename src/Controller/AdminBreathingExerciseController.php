<?php

namespace App\Controller;

use App\Entity\BreathingExercise;
use App\Form\BreathingExerciseType;
use App\Repository\BreathingExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Admin routes disabled
class AdminBreathingExerciseController extends AbstractController
{
    public function index(BreathingExerciseRepository $exerciseRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $exercises = $exerciseRepository->findAll();

        return $this->render('admin/exercise/index.html.twig', [
            'exercises' => $exercises,
        ]);
    }

    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $exercise = new BreathingExercise();
        $form = $this->createForm(BreathingExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($exercise);
            $em->flush();

            $this->addFlash('success', sprintf('Exercice "%s" créé.', $exercise->getName()));

            return $this->redirectToRoute('admin_exercise_index');
        }

        return $this->render('admin/exercise/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, BreathingExercise $exercise, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(BreathingExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', sprintf('Exercice "%s" modifié.', $exercise->getName()));

            return $this->redirectToRoute('admin_exercise_index');
        }

        return $this->render('admin/exercise/edit.html.twig', [
            'form' => $form->createView(),
            'exercise' => $exercise,
        ]);
    }

    public function delete(Request $request, BreathingExercise $exercise, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $exercise->getId(), $request->request->get('_token'))) {
            $exerciseName = $exercise->getName();
            $em->remove($exercise);
            $em->flush();

            $this->addFlash('success', sprintf('Exercice "%s" supprimé.', $exerciseName));
        }

        return $this->redirectToRoute('admin_exercise_index');
    }
}

