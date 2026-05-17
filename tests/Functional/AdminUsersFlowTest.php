<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUsersFlowTest extends AbstractFunctionalTestCase
{
    public function testNonAdminCannotAccessUsersAdministration(): void
    {
        $user = $this->createUser('simple-user@example.com', 'Utilisateur', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanSearchCreateDeactivateReactivateAndDeleteUsers(): void
    {
        $admin = $this->createUser('admin-users@example.com', 'Admin', 'password123', true);
        $target = $this->createUser('target-user@example.com', 'Cible', 'password123');
        $this->createUser('other-user@example.com', 'Autre', 'password123');
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/users?q=Cible');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Résultats de recherche pour');
        $this->assertSelectorTextContains('body', 'target-user@example.com');
        $this->assertSelectorTextNotContains('body', 'other-user@example.com');

        $crawler = $this->client->request('GET', '/admin/users/create');
        $form = $crawler->selectButton("Créer l'Utilisateur")->form([
            'admin_create_user[name]' => 'Nouveau membre',
            'admin_create_user[email]' => 'created-by-admin@example.com',
            'admin_create_user[password]' => 'secret123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/users');

        $this->em->clear();
        /** @var User|null $created */
        $created = $this->em->getRepository(User::class)->findOneBy(['email' => 'created-by-admin@example.com']);
        $this->assertInstanceOf(User::class, $created);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($created, 'secret123'));

        $crawler = $this->client->request('GET', '/admin/users');
        $deactivateForm = $crawler->selectButton('Désactiver')->form();
        $this->client->submit($deactivateForm);
        $this->assertResponseRedirects('/admin/users');

        $this->em->clear();
        $deactivatedTarget = $this->em->getRepository(User::class)->find($target->getId());
        $this->assertInstanceOf(User::class, $deactivatedTarget);
        $this->assertFalse($deactivatedTarget->isActive());

        $crawler = $this->client->request('GET', '/admin/users');
        $activateForm = $crawler->selectButton('Activer')->form();
        $this->client->submit($activateForm);
        $this->assertResponseRedirects('/admin/users');

        $this->em->clear();
        $reactivatedTarget = $this->em->getRepository(User::class)->find($target->getId());
        $this->assertInstanceOf(User::class, $reactivatedTarget);
        $this->assertTrue($reactivatedTarget->isActive());

        $crawler = $this->client->request('GET', '/admin/users');
        $deleteForm = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($deleteForm);
        $this->assertResponseRedirects('/admin/users');

        $this->em->clear();
        $this->assertNull($this->em->getRepository(User::class)->find($target->getId()));
    }

    public function testAdminCannotDeactivateOrDeleteTheirOwnAccount(): void
    {
        $admin = $this->createUser('self-admin@example.com', 'Admin', 'password123', true);
        $target = $this->createUser('token-source@example.com', 'Cible', 'password123');
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/users');
        $deactivateToken = $crawler->filter(sprintf('form[action="/admin/users/%d/deactivate"] input[name="_token"]', $target->getId()))->attr('value');
        $deleteToken = $crawler->filter(sprintf('form[action="/admin/users/%d/delete"] input[name="_token"]', $target->getId()))->attr('value');

        $this->client->request('POST', '/admin/users/'.$admin->getId().'/deactivate', ['_token' => $deactivateToken]);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();

        $this->client->request('POST', '/admin/users/'.$admin->getId().'/delete', ['_token' => $deleteToken]);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();

        $this->em->clear();
        $reloadedAdmin = $this->em->getRepository(User::class)->find($admin->getId());
        $this->assertInstanceOf(User::class, $reloadedAdmin);
        $this->assertTrue($reloadedAdmin->isActive());
        $this->assertNotNull($this->em->getRepository(User::class)->find($target->getId()));
    }
}
