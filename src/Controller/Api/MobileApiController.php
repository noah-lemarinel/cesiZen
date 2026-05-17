<?php

namespace App\Controller\Api;

use App\Entity\BreathingExercise;
use App\Entity\EmotionEntry;
use App\Repository\BreathingExerciseRepository;
use App\Repository\EmotionEntryRepository;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Small JSON API used by the mobile SPA. It intentionally keeps things simple and uses the existing session-based
 * authentication: the browser will send the session cookie on requests from the same origin.
 */
#[Route('/api')]
class MobileApiController extends AbstractController
{
    #[Route('/emotions', name: 'api_emotions', methods: ['GET'])]
    public function emotions(EmotionRepository $emotionRepository): JsonResponse
    {
        $emotions = $emotionRepository->findAll();

        $data = array_map(fn($e) => [
            'id' => $e->getId(),
            'name' => $e->getName(),
            'parent' => $e->getParent()?->getId(),
        ], $emotions);

        return $this->json($data);
    }

    #[Route('/exercises', name: 'api_exercises', methods: ['GET'])]
    public function exercises(BreathingExerciseRepository $repo): JsonResponse
    {
        $exercises = $repo->findAll();

        $data = array_map(fn(BreathingExercise $b) => [
            'id' => $b->getId(),
            'name' => $b->getName(),
            'title' => $b->getName(),
            'description' => $b->getDescription(),
            'inhaleSeconds' => $b->getInhaleSeconds(),
            'holdSeconds' => $b->getHoldSeconds(),
            'exhaleSeconds' => $b->getExhaleSeconds(),
            'cycles' => $b->getCycles(),
        ], $exercises);

        return $this->json($data);
    }

    #[Route('/entries', name: 'api_entries', methods: ['GET'])]
    public function entries(EmotionEntryRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $entries = $repo->findByUser($this->getUser());

        $data = array_map(fn(EmotionEntry $e) => [
            'id' => $e->getId(),
            'emotion' => $e->getEmotion()->getName(),
            'emotion_id' => $e->getEmotion()->getId(),
            'notes' => $e->getNotes(),
            'created_at' => $e->getCreatedAt()?->format(DATE_ATOM),
        ], $entries);

        return $this->json($data);
    }

    #[Route('/entries', name: 'api_entries_create', methods: ['POST'])]
    public function createEntry(Request $request, EmotionRepository $emotionRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = json_decode($request->getContent(), true);
        $emotionId = $data['emotion_id'] ?? null;
        $notes = $data['notes'] ?? null;

        if (!$emotionId) {
            return $this->json(['error' => 'emotion_id is required'], 400);
        }

        $emotion = $emotionRepository->find($emotionId);
        if (!$emotion) {
            return $this->json(['error' => 'emotion not found'], 404);
        }

        $entry = new EmotionEntry();
        $entry->setEmotion($emotion);
        $entry->setNotes($notes);
        $entry->setUser($this->getUser());

        $em->persist($entry);
        $em->flush();

        return $this->json(['ok' => true, 'id' => $entry->getId()]);
    }
}
