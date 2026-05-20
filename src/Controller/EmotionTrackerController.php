<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Entity\EmotionEntry;
use App\Entity\User;
use App\Form\EmotionEntryType;
use App\Form\EmotionType;
use App\Repository\EmotionEntryRepository;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmotionTrackerController extends AbstractController
{
    #[Route('/emotion/tracker', name: 'emotion_tracker_index')]
    public function index(EmotionRepository $emotionRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Show palette d'émotions for admins
        // For regular users, redirect to journal
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            return $this->redirectToRoute('emotion_tracker_journal');
        }

        // Admin view - show emotion management
        $emotions = $emotionRepository->findAll();

        return $this->render('emotion_tracker/index.html.twig', [
            'emotions' => $emotions,
        ]);
    }

    #[Route('/emotion/tracker/journal', name: 'emotion_tracker_journal')]
    public function journal(EmotionEntryRepository $emotionEntryRepository, Request $request): Response
    {
        // Require authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get only emotions for the current user
        $entries = $emotionEntryRepository->findByUser($this->getUser());

        return $this->render('emotion_tracker/journal.html.twig', [
            'emotions' => $entries,
        ]);
    }

    #[Route('/emotion/tracker/create', name: 'emotion_tracker_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Require admin authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('Seuls les administrateurs peuvent créer des émotions.');
        }

        $emotion = new Emotion();
        $emotionForm = $this->createForm(EmotionType::class, $emotion);
        $emotionForm->handleRequest($request);

        if ($emotionForm->isSubmitted() && $emotionForm->isValid()) {
            $entityManager->persist($emotion);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Émotion "%s" créée avec succès !', $emotion->getName()));

            return $this->redirectToRoute('emotion_tracker_index');
        }

        return $this->render('emotion_tracker/create.html.twig', [
            'emotionForm' => $emotionForm->createView(),
        ]);
    }

    #[Route('/emotion/tracker/delete-emotion/{id}', name: 'emotion_tracker_delete_emotion', methods: ['POST'])]
    public function deleteEmotion(int $id, EmotionRepository $emotionRepository, EntityManagerInterface $entityManager): Response
    {
        // Require admin authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('Seuls les administrateurs peuvent supprimer des émotions.');
        }

        $emotion = $emotionRepository->find($id);

        if (!$emotion) {
            $this->addFlash('error', 'Émotion introuvable.');

            return $this->redirectToRoute('emotion_tracker_index');
        }

        // Check if emotion has children
        if ($emotion->getChildren()->count() > 0) {
            $this->addFlash('error', sprintf('Impossible de supprimer "%s" car elle contient des sous-émotions. Supprimez d\'abord les sous-émotions.', $emotion->getName()));

            return $this->redirectToRoute('emotion_tracker_index');
        }

        // Check if emotion is used in entries
        $emotionName = $emotion->getName();

        // Delete the emotion
        $entityManager->remove($emotion);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Émotion "%s" supprimée avec succès.', $emotionName));

        return $this->redirectToRoute('emotion_tracker_index');
    }

    #[Route('/emotion/tracker/add', name: 'emotion_tracker_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Require authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Use a simple form for selecting an existing emotion and adding notes
        $form = $this->createForm(EmotionEntryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // create and persist an EmotionEntry
            $entry = new EmotionEntry();
            $entry->setEmotion($data['emotion']);
            $entry->setNotes($data['notes'] ?? null);
            $entry->setUser($currentUser);

            $entityManager->persist($entry);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Émotion enregistrée: %s — %s', $data['emotion']->getName(), substr((string) ($data['notes'] ?? ''), 0, 200)));

            return $this->redirectToRoute('emotion_tracker_journal');
        }

        return $this->render('emotion_tracker/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/emotion/tracker/delete/{id}', name: 'emotion_tracker_delete', methods: ['POST'])]
    public function delete(int $id, EmotionEntryRepository $emotionEntryRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Require authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $entry = $emotionEntryRepository->find($id);

        if (!$entry) {
            $this->addFlash('error', 'Émotion introuvable.');

            return $this->redirectToRoute('emotion_tracker_journal');
        }

        // Verify ownership
        if ($entry->getUser() !== $currentUser) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer l\'émotion d\'un autre utilisateur.');
        }

        $emotionName = $entry->getEmotion()->getName();
        $entityManager->remove($entry);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Émotion "%s" supprimée.', $emotionName));

        return $this->redirectToRoute('emotion_tracker_journal');
    }

    #[Route('/emotion/tracker/report', name: 'emotion_tracker_report')]
    public function report(EmotionEntryRepository $emotionEntryRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Require authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $period = $request->query->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = new \DateTimeImmutable();

        // Get entries for the user in the given period
        $entries = $emotionEntryRepository->findByUserAndPeriod(
            $this->getUser(),
            $startDate,
            $endDate
        );

        // Count emotions
        $emotionCounts = [];
        $emotionsByParent = [];

        foreach ($entries as $entry) {
            $emotion = $entry->getEmotion();
            $parentName = $emotion->getParent()?->getName() ?? $emotion->getName();

            if (!isset($emotionCounts[$emotion->getName()])) {
                $emotionCounts[$emotion->getName()] = 0;
            }
            ++$emotionCounts[$emotion->getName()];

            if (!isset($emotionsByParent[$parentName])) {
                $emotionsByParent[$parentName] = 0;
            }
            ++$emotionsByParent[$parentName];
        }

        arsort($emotionCounts);
        arsort($emotionsByParent);

        return $this->render('emotion_tracker/report.html.twig', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'entries' => $entries,
            'emotionCounts' => $emotionCounts,
            'emotionsByParent' => $emotionsByParent,
            'totalEntries' => count($entries),
        ]);
    }

    private function getStartDate(string $period): \DateTimeImmutable
    {
        $today = new \DateTimeImmutable();

        return match ($period) {
            'week' => $today->modify('-7 days'),
            'month' => $today->modify('-1 month'),
            'quarter' => $today->modify('-3 months'),
            'year' => $today->modify('-1 year'),
            default => $today->modify('-1 month'),
        };
    }

    #[Route('/emotion/tracker/api/sync', name: 'emotion_tracker_api_sync', methods: ['POST'])]
    public function syncLocalStorage(Request $request, EntityManagerInterface $entityManager, EmotionRepository $emotionRepository): JsonResponse
    {
        // Require authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $entries = $data['entries'] ?? [];

            $syncedCount = 0;

            foreach ($entries as $entry) {
                // Skip if already has a numeric ID (already synced)
                if (is_numeric($entry['id'])) {
                    continue;
                }

                // Find the emotion by ID
                $emotion = $emotionRepository->find($entry['emotionId']);
                if (!$emotion) {
                    continue;
                }

                // Create and persist an EmotionEntry
                $emotionEntry = new EmotionEntry();
                $emotionEntry->setEmotion($emotion);
                $emotionEntry->setNotes($entry['notes'] ?? null);
                $emotionEntry->setUser($currentUser);

                $entityManager->persist($emotionEntry);
                ++$syncedCount;
            }

            if ($syncedCount > 0) {
                $entityManager->flush();
            }

            return new JsonResponse([
                'success' => true,
                'synced' => $syncedCount,
                'message' => sprintf('%d emotion(s) synced successfully', $syncedCount),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
