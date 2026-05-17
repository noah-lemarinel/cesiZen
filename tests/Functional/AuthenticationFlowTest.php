<?php

namespace App\Tests\Functional;

use App\Entity\User;

class AuthenticationFlowTest extends AbstractFunctionalTestCase
{
    public function testRegistrationCreatesAccountAndLogsUserIn(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#register-form');

        $form = $crawler->selectButton("S'inscrire")->form([
            'name' => 'Nouveau Utilisateur',
            'email' => 'registration@example.com',
            'password' => 'password123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Mon Compte');
        $this->assertSelectorTextContains('body', 'Se déconnecter');

        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'registration@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
    }

    public function testLoginAuthenticatesExistingUser(): void
    {
        $this->createUser('login@example.com', 'Login User', 'password123');

        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#login-form');

        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Mon Compte');
        $this->assertSelectorTextContains('body', 'Login User');
    }

    public function testDisabledUserCannotLogin(): void
    {
        $user = $this->createUser('disabled@example.com', 'Disabled User', 'password123', false, false);

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'disabled@example.com',
            'password' => 'password123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();
        $this->assertSelectorExists('#login-form');

        $this->em->clear();
        /** @var User|null $reloadedUser */
        $reloadedUser = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertInstanceOf(User::class, $reloadedUser);
        $this->assertFalse($reloadedUser->isActive());
    }
}
