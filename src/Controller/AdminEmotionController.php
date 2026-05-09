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

class AdminEmotionController extends AbstractController
{
    #[Route('/admin/emotions', name: 'admin_emotion_index')]
    public function index(EmotionRepository $emotionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $emotions = $emotionRepository->findAll();

        return $this->render('admin/emotion/index.html.twig', [
            'emotions' => $emotions,
        ]);
    }

    #[Route('/admin/emotions/new', name: 'admin_emotion_new')]
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

