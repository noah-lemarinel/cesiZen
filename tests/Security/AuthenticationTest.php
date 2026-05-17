<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationTest extends WebTestCase
{
    private static function ensureSchemaFromEntityManager($em): void
    {
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($em);
            // Drop and create to have a clean DB for tests
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    public function testRegisterAndAutoLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        self::ensureSchemaFromEntityManager($em);

        $crawler = $client->request('GET', '/register');

        $formNode = $crawler->filter('#register-form');
        if (0 === $formNode->count()) {
            @mkdir(dirname(__DIR__).'/var', 0777, true);
            file_put_contents(dirname(__DIR__).'/var/test-debug-register.html', $client->getResponse()->getContent());
        }
        $this->assertGreaterThan(0, $formNode->count(), 'Register form not found on the page');

        $form = $formNode->form([
            'name' => 'Test User',
            'email' => 'test+auto@example.com',
            'password' => 'password123',
        ]);

        $client->submit($form);

        // After successful registration + auto-login the user should be redirected (302)
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();

        // Check the response contains the logged-in navigation entry
        $this->assertStringContainsString('Mon Compte', $client->getResponse()->getContent());
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        self::ensureSchemaFromEntityManager($em);

        // Create a user
        $user = new User();
        $user->setEmail('testlogin@example.com');
        // Use the password hasher to hash the password
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $hashed = $passwordHasher->hashPassword($user, 'testpassword');
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        $crawler = $client->request('GET', '/login');

        $formNode = $crawler->filter('#login-form');
        if (0 === $formNode->count()) {
            @mkdir(dirname(__DIR__).'/var', 0777, true);
            file_put_contents(dirname(__DIR__).'/var/test-debug-login.html', $client->getResponse()->getContent());
        }
        $this->assertGreaterThan(0, $formNode->count(), 'Login form not found on the page');

        $form = $formNode->form([
            'email' => 'testlogin@example.com',
            'password' => 'testpassword',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();

        // Confirm the logged-in navigation is visible
        $this->assertStringContainsString('Mon Compte', $client->getResponse()->getContent());
    }
}
