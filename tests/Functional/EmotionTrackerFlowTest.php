<?php

namespace App\Tests\Functional;

use App\Entity\Emotion;
use App\Entity\EmotionEntry;

class EmotionTrackerFlowTest extends AbstractFunctionalTestCase
{
    public function testRegularUserIsRedirectedFromAdminEmotionTrackerToJournal(): void
    {
        $user = $this->createUser('tracker-user@example.com', 'Tracker', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/emotion/tracker');

        $this->assertResponseRedirects('/emotion/tracker/journal');
    }

    public function testAdminCanAccessEmotionManagementPage(): void
    {
        $admin = $this->createUser('emotion-admin@example.com', 'Admin', 'password123', true);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/emotion/tracker');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Gestion des Émotions');
    }

    public function testUserCanAddEmotionAndSeeItInJournal(): void
    {
        $user = $this->createUser('journal-user@example.com', 'Journal', 'password123');
        $primary = $this->createEmotion('Sérénité', 'Émotion principale', '#00AA00');
        $secondary = $this->createEmotion('Calme', 'Sous-émotion', '#22CC88', $primary);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/emotion/tracker/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form([
            'emotion_entry[emotion]' => (string) $secondary->getId(),
            'emotion_entry[notes]' => 'Je me sens bien aujourd\'hui.',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/emotion/tracker/journal');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Calme');
        $this->assertSelectorTextContains('body', 'Je me sens bien aujourd\'hui.');

        /** @var EmotionEntry|null $entry */
        $entry = $this->em->getRepository(EmotionEntry::class)->findOneBy(['user' => $user]);
        $this->assertInstanceOf(EmotionEntry::class, $entry);
        $this->assertSame($secondary->getId(), $entry->getEmotion()?->getId());
    }

    public function testUserCanDeleteOwnEmotionEntry(): void
    {
        $user = $this->createUser('delete-entry@example.com', 'Journal', 'password123');
        $primary = $this->createEmotion('Stress', null, '#FF0000');
        $secondary = $this->createEmotion('Tension', null, '#CC2222', $primary);
        $entry = $this->createEmotionEntry($user, $secondary, 'À supprimer');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/emotion/tracker/journal');
        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/emotion/tracker/journal');
        $this->em->clear();
        $this->assertNull($this->em->getRepository(EmotionEntry::class)->find($entry->getId()));
    }

    public function testUserCannotDeleteAnotherUsersEmotionEntry(): void
    {
        $owner = $this->createUser('owner@example.com', 'Propriétaire', 'password123');
        $otherUser = $this->createUser('intruder@example.com', 'Intrus', 'password123');
        $primary = $this->createEmotion('Joie', null, '#00DD66');
        $secondary = $this->createEmotion('Contentement', null, '#55EE99', $primary);
        $entry = $this->createEmotionEntry($owner, $secondary, 'Privé');
        $this->client->loginUser($otherUser);

        $this->client->request('POST', '/emotion/tracker/delete/'.$entry->getId());

        $this->assertResponseStatusCodeSame(403);
        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(EmotionEntry::class)->find($entry->getId()));
    }

    public function testEmotionReportFiltersByPeriod(): void
    {
        $user = $this->createUser('report@example.com', 'Rapport', 'password123');
        $primary = $this->createEmotion('Fatigue', null, '#444444');
        $recent = $this->createEmotion('Repos', null, '#777777', $primary);
        $old = $this->createEmotion('Épuisement', null, '#111111', $primary);

        $recentEntry = $this->createEmotionEntry($user, $recent, 'Entrée récente');
        $oldEntry = $this->createEmotionEntry($user, $old, 'Entrée ancienne');
        $this->setPrivateProperty($oldEntry, 'createdAt', new \DateTimeImmutable('-2 months'));
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/emotion/tracker/report?period=month');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Résumé');
        $this->assertSelectorTextContains('body', '1 entrées enregistrées');
        $this->assertSelectorTextContains('body', 'Repos');
        $this->assertSelectorTextNotContains('body', 'Épuisement');
    }

    public function testAdminCanCreateAndBlockDeletionOfEmotionWithChildren(): void
    {
        $admin = $this->createUser('emotion-admin2@example.com', 'Admin', 'password123', true);
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/emotion/tracker/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form')->form([
            'emotion[name]' => 'Peur',
            'emotion[description]' => 'Émotion principale',
            'emotion[color]' => '#AA0000',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/emotion/tracker');

        $this->em->clear();
        /** @var Emotion $primary */
        $primary = $this->em->getRepository(Emotion::class)->findOneBy(['name' => 'Peur']);
        $this->assertInstanceOf(Emotion::class, $primary);

        $child = $this->createEmotion('Inquiétude', 'Sous-émotion', '#BB2222', $primary);

        $this->client->request('POST', '/emotion/tracker/delete-emotion/'.$primary->getId());
        $this->assertResponseRedirects('/emotion/tracker');
        $this->client->followRedirect();

        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(Emotion::class)->find($primary->getId()));
        $this->assertNotNull($this->em->getRepository(Emotion::class)->find($child->getId()));
    }
}
