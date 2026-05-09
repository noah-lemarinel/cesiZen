<?php

namespace App\Controller;

use App\Repository\BreathingExerciseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExercisesController extends AbstractController
{
    #[Route('/exercises', name: 'exercises_index')]
    public function index(BreathingExerciseRepository $exerciseRepository): Response
    {
        $exercises = $exerciseRepository->findAll();

        return $this->render('exercises/index.html.twig', [
            'exercises' => $exercises,
        ]);
    }

    #[Route('/exercises/{id}', name: 'exercises_show')]
    public function show(BreathingExerciseRepository $exerciseRepository, int $id): Response
    {
        $exercise = $exerciseRepository->find($id);

        if (!$exercise) {
            throw $this->createNotFoundException('Exercice non trouvé.');
        }

        return $this->render('exercises/show.html.twig', [
            'exercise' => $exercise,
        ]);
    }
}

