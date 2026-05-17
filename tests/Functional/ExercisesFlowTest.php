<?php

namespace App\Tests\Functional;

use App\Entity\BreathingExercise;

class ExercisesFlowTest extends AbstractFunctionalTestCase
{
    public function testGuestsCanSeeOnlyDefaultExercises(): void
    {
        $author = $this->createUser('exercise-author@example.com', 'Auteur', 'password123');
        $defaultExercise = $this->createExercise(null, 'Cohérence cardiaque', 'Exercice public', 5, 5, 5, 6);
        $userExercise = $this->createExercise($author, 'Exercice personnel', 'Visible uniquement pour le propriétaire', 4, 4, 4, 4);

        $this->client->request('GET', '/exercises');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Exercices de respiration');
        $this->assertSelectorTextContains('body', 'Cohérence cardiaque');
        $this->assertSelectorTextNotContains('body', 'Exercice personnel');
    }

    public function testAnyoneCanReadAnExercise(): void
    {
        $exercise = $this->createExercise(null, 'Lecture', 'Description', 4, 4, 4, 4);

        $this->client->request('GET', '/exercises/'.$exercise->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Lecture');
        $this->assertSelectorTextContains('body', 'Description');
    }

    public function testLoggedInUserCanCreateEditAndDeleteOwnExercise(): void
    {
        $user = $this->createUser('exercise-user@example.com', 'Utilisateur', 'password123');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/exercises/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer')->form([
            'breathing_exercise[name]' => 'Respiration 4-4-4',
            'breathing_exercise[description]' => 'Créer pour la recette',
            'breathing_exercise[inhaleSeconds]' => '4',
            'breathing_exercise[holdSeconds]' => '4',
            'breathing_exercise[exhaleSeconds]' => '4',
            'breathing_exercise[cycles]' => '5',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects();

        $this->em->clear();
        /** @var BreathingExercise $exercise */
        $exercise = $this->em->getRepository(BreathingExercise::class)->findOneBy(['name' => 'Respiration 4-4-4']);
        $this->assertInstanceOf(BreathingExercise::class, $exercise);
        $this->assertSame($user->getId(), $exercise->getCreatedBy()?->getId());

        $crawler = $this->client->request('GET', '/exercises/'.$exercise->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $editForm = $crawler->selectButton('Modifier')->form([
            'breathing_exercise[name]' => 'Respiration 5-5-5',
            'breathing_exercise[description]' => 'Mise à jour',
            'breathing_exercise[inhaleSeconds]' => '5',
            'breathing_exercise[holdSeconds]' => '5',
            'breathing_exercise[exhaleSeconds]' => '5',
            'breathing_exercise[cycles]' => '6',
        ]);

        $this->client->submit($editForm);
        $this->assertResponseRedirects('/exercises/'.$exercise->getId());

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Respiration 5-5-5');
        $this->assertSelectorTextContains('body', 'Mise à jour');

        $crawler = $this->client->request('GET', '/exercises/'.$exercise->getId());
        $deleteForm = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/exercises');
        $this->em->clear();
        $this->assertNull($this->em->getRepository(BreathingExercise::class)->find($exercise->getId()));
    }

    public function testUserCannotModifyAnotherUsersExercise(): void
    {
        $owner = $this->createUser('owner-exercise@example.com', 'Propriétaire', 'password123');
        $intruder = $this->createUser('intruder-exercise@example.com', 'Intrus', 'password123');
        $exercise = $this->createExercise($owner, 'Privé', 'Exercice privé', 4, 4, 4, 4);
        $this->client->loginUser($intruder);

        $this->client->request('GET', '/exercises/'.$exercise->getId().'/edit');
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('POST', '/exercises/'.$exercise->getId().'/delete', ['_token' => 'fake']);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCreatesDefaultExerciseWithoutOwner(): void
    {
        $admin = $this->createUser('exercise-admin@example.com', 'Admin', 'password123', true);
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/exercises/create');
        $form = $crawler->selectButton('Créer')->form([
            'breathing_exercise[name]' => 'Exercice par défaut',
            'breathing_exercise[description]' => 'Créé par un administrateur',
            'breathing_exercise[inhaleSeconds]' => '3',
            'breathing_exercise[holdSeconds]' => '3',
            'breathing_exercise[exhaleSeconds]' => '3',
            'breathing_exercise[cycles]' => '4',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects();

        $this->em->clear();
        /** @var BreathingExercise $exercise */
        $exercise = $this->em->getRepository(BreathingExercise::class)->findOneBy(['name' => 'Exercice par défaut']);
        $this->assertInstanceOf(BreathingExercise::class, $exercise);
        $this->assertNull($exercise->getCreatedBy());
    }
}
