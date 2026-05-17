<?php

namespace App\Tests\Functional;

use App\Entity\BlogPost;
use App\Entity\BreathingExercise;
use App\Entity\Emotion;
use App\Entity\EmotionEntry;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractFunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->rebuildSchema();
    }

    protected function tearDown(): void
    {
        if (isset($this->em)) {
            $this->em->clear();
        }

        self::ensureKernelShutdown();
        parent::tearDown();
    }

    protected function rebuildSchema(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if ([] === $metadata) {
            return;
        }

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function createUser(
        string $email,
        ?string $name = null,
        string $plainPassword = 'password123',
        bool $isAdmin = false,
        bool $isActive = true,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setIsAdmin($isAdmin);
        $user->setIsActive($isActive);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();
        $this->em->refresh($user);

        return $user;
    }

    protected function createBlogPost(
        User $author,
        string $title,
        string $content,
        bool $isPublished = true,
        ?\DateTimeInterface $updatedAt = null,
    ): BlogPost {
        $post = new BlogPost();
        $post->setAuthor($author);
        $post->setTitle($title);
        $post->setContent($content);
        $post->setIsPublished($isPublished);
        $post->setUpdatedAt($updatedAt);

        $this->em->persist($post);
        $this->em->flush();
        $this->em->refresh($post);

        return $post;
    }

    protected function createExercise(
        ?User $createdBy,
        string $name,
        string $description,
        int $inhaleSeconds,
        int $holdSeconds,
        int $exhaleSeconds,
        int $cycles,
    ): BreathingExercise {
        $exercise = new BreathingExercise();
        $exercise->setCreatedBy($createdBy);
        $exercise->setName($name);
        $exercise->setDescription($description);
        $exercise->setInhaleSeconds($inhaleSeconds);
        $exercise->setHoldSeconds($holdSeconds);
        $exercise->setExhaleSeconds($exhaleSeconds);
        $exercise->setCycles($cycles);

        $this->em->persist($exercise);
        $this->em->flush();
        $this->em->refresh($exercise);

        return $exercise;
    }

    protected function createEmotion(
        string $name,
        ?string $description = null,
        ?string $color = null,
        ?Emotion $parent = null,
    ): Emotion {
        $emotion = new Emotion();
        $emotion->setName($name);
        $emotion->setDescription($description);
        $emotion->setColor($color);
        $emotion->setParent($parent);

        $this->em->persist($emotion);
        $this->em->flush();
        $this->em->refresh($emotion);

        if (null !== $parent) {
            $this->em->refresh($parent);
        }

        return $emotion;
    }

    protected function createEmotionEntry(
        User $user,
        Emotion $emotion,
        ?string $notes = null,
        ?\DateTimeImmutable $createdAt = null,
    ): EmotionEntry {
        $entry = new EmotionEntry();
        $entry->setUser($user);
        $entry->setEmotion($emotion);
        $entry->setNotes($notes);

        if (null !== $createdAt) {
            $this->setPrivateProperty($entry, 'createdAt', $createdAt);
        }

        $this->em->persist($entry);
        $this->em->flush();
        $this->em->refresh($entry);

        return $entry;
    }

    protected function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setValue($object, $value);
    }

    protected function setEntityManagerTimestamp(object $object, string $property, \DateTimeInterface $value): void
    {
        $this->setPrivateProperty($object, $property, $value);
    }
}
