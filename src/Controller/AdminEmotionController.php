<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Form\EmotionType;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Admin routes disabled - use EmotionTrackerController instead
class AdminEmotionController extends AbstractController
{
    public function index(EmotionRepository $emotionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $emotions = $emotionRepository->findAll();

        return $this->render('admin/emotion/index.html.twig', [
            'emotions' => $emotions,
        ]);
    }

    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $emotion = new Emotion();
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($emotion);
            $em->flush();

            $this->addFlash('success', sprintf('Émotion "%s" créée.', $emotion->getName()));

            return $this->redirectToRoute('admin_emotion_index');
        }

        return $this->render('admin/emotion/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
