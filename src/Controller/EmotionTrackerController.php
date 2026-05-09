<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Form\EmotionEntryType;
use App\Form\EmotionType;
use App\Repository\EmotionEntryRepository;
use App\Entity\EmotionEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmotionTrackerController extends AbstractController
{
    #[Route('/emotion/tracker', name: 'emotion_tracker_index')]
    public function index(EmotionEntryRepository $emotionEntryRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $emotions = $emotionEntryRepository->findAll();

        $emotionForm = null;
        // Only allow admins to create emotions
        if ($this->getUser() && $this->getUser()->isAdmin()) {
            $emotionForm = $this->createForm(EmotionType::class);
            $emotionForm->handleRequest($request);

            if ($emotionForm->isSubmitted() && $emotionForm->isValid()) {
                $emotion = $emotionForm->getData();
                $entityManager->persist($emotion);
                $entityManager->flush();

                $this->addFlash('success', sprintf('Émotion "%s" créée.', $emotion->getName()));

                return $this->redirectToRoute('emotion_tracker_index');
            }
        }

        return $this->render('emotion_tracker/index.html.twig', [
            'emotions' => $emotions,
            'emotionForm' => $emotionForm ? $emotionForm->createView() : null,
        ]);
    }

    #[Route('/emotion/tracker/add', name: 'emotion_tracker_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Use a simple form for selecting an existing emotion and adding notes
        $form = $this->createForm(EmotionEntryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // create and persist an EmotionEntry
            $entry = new EmotionEntry();
            $entry->setEmotion($data['emotion']);
            $entry->setNotes($data['notes'] ?? null);
            if ($this->getUser()) {
                $entry->setUser($this->getUser());
            }

            $entityManager->persist($entry);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Émotion enregistrée: %s — %s', $data['emotion']->getName(), substr((string) ($data['notes'] ?? ''), 0, 200)));

            return $this->redirectToRoute('emotion_tracker_index');
        }

        return $this->render('emotion_tracker/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
