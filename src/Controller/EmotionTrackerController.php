<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Form\EmotionType;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmotionTrackerController extends AbstractController
{
    #[Route('/emotion/tracker', name: 'emotion_tracker_index')]
    public function index(EmotionRepository $emotionRepository): Response
    {
        $emotions = $emotionRepository->findAll();

        return $this->render('emotion_tracker/index.html.twig', [
            'emotions' => $emotions,
        ]);
    }

    #[Route('/emotion/tracker/add', name: 'emotion_tracker_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emotion = new Emotion();
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emotion);
            $entityManager->flush();

            return $this->redirectToRoute('emotion_tracker_index');
        }

        return $this->render('emotion_tracker/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
