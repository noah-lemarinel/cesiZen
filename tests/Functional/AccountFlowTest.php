<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountFlowTest extends AbstractFunctionalTestCase
{
    public function testAnonymousUsersAreRedirectedFromAccountPage(): void
    {
        $this->client->request('GET', '/account');

        $this->assertResponseRedirects('/login');
    }

    public function testLoggedInUserCanSeeAccountPage(): void
    {
        $user = $this->createUser('account@example.com', 'Compte Test', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Mon Compte');
        $this->assertSelectorTextContains('body', 'account@example.com');
        $this->assertSelectorTextContains('body', 'Compte Test');
        $this->assertSelectorTextContains('body', 'Utilisateur');
    }

    public function testUserCanUpdateOwnAccountInformation(): void
    {
        $user = $this->createUser('before@example.com', 'Avant', 'password123');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/account/edit');
        $form = $crawler->selectButton('Enregistrer les Modifications')->form([
            'account_update[name]' => 'Après',
            'account_update[email]' => 'after@example.com',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/account');

        $this->em->clear();
        /** @var User|null $updatedUser */
        $updatedUser = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertSame('Après', $updatedUser->getName());
        $this->assertSame('after@example.com', $updatedUser->getEmail());
    }

    public function testAccountEditRejectsEmailAlreadyUsedByAnotherUser(): void
    {
        $currentUser = $this->createUser('current@example.com', 'Courant', 'password123');
        $otherUser = $this->createUser('other@example.com', 'Autre', 'password123');
        $this->client->loginUser($currentUser);

        $crawler = $this->client->request('GET', '/account/edit');
        $form = $crawler->selectButton('Enregistrer les Modifications')->form([
            'account_update[name]' => 'Courant modifié',
            'account_update[email]' => 'other@example.com',
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        /** @var User|null $reloadedCurrentUser */
        $reloadedCurrentUser = $this->em->getRepository(User::class)->find($currentUser->getId());
        $this->assertInstanceOf(User::class, $reloadedCurrentUser);
        $this->assertSame('current@example.com', $reloadedCurrentUser->getEmail());
        $this->assertSame('Courant', $reloadedCurrentUser->getName());
        $this->assertSame('other@example.com', $otherUser->getEmail());
    }

    public function testUserCanChangePassword(): void
    {
        $user = $this->createUser('password-change@example.com', 'Mot de passe', 'old-password');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/account/password');
        $form = $crawler->selectButton('Changer le Mot de Passe')->form([
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'confirm_password' => 'new-password',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/account');

        $this->em->clear();
        /** @var User|null $reloadedUser */
        $reloadedUser = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertInstanceOf(User::class, $reloadedUser);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($reloadedUser, 'new-password'));
    }

    public function testPasswordChangeRejectsWrongCurrentPassword(): void
    {
        $user = $this->createUser('wrong-current@example.com', 'Erreur', 'old-password');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/account/password');
        $form = $crawler->selectButton('Changer le Mot de Passe')->form([
            'current_password' => 'bad-password',
            'new_password' => 'new-password',
            'confirm_password' => 'new-password',
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        /** @var User|null $reloadedUser */
        $reloadedUser = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertInstanceOf(User::class, $reloadedUser);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($reloadedUser, 'old-password'));
        $this->assertFalse($hasher->isPasswordValid($reloadedUser, 'new-password'));
    }

    public function testPasswordChangeRejectsMismatchedConfirmation(): void
    {
        $user = $this->createUser('mismatch@example.com', 'Erreur', 'old-password');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/account/password');
        $form = $crawler->selectButton('Changer le Mot de Passe')->form([
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'confirm_password' => 'different-password',
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        /** @var User|null $reloadedUser */
        $reloadedUser = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertInstanceOf(User::class, $reloadedUser);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($reloadedUser, 'old-password'));
        $this->assertFalse($hasher->isPasswordValid($reloadedUser, 'new-password'));
    }

    public function testUserCanDeleteOwnAccountWithValidCsrfToken(): void
    {
        $user = $this->createUser('delete-me@example.com', 'Suppression', 'password123');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/account');
        $form = $crawler->selectButton('Supprimer le Compte')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/logout');

        $this->em->clear();
        $this->assertNull($this->em->getRepository(User::class)->find($user->getId()));
    }
}
