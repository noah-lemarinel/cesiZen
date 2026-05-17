<?php

namespace App\Tests\Functional;

use App\Entity\BlogPost;

class ResourcesFlowTest extends AbstractFunctionalTestCase
{
    public function testGuestsCanSeePublishedResourcesOnly(): void
    {
        $author = $this->createUser('author@example.com', 'Auteur', 'password123');
        $published = $this->createBlogPost($author, 'Article publié', 'Contenu publié', true);
        $unpublished = $this->createBlogPost($author, 'Article brouillon', 'Contenu brouillon', false);

        $this->client->request('GET', '/ressources');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Ressources');
        $this->assertSelectorTextContains('body', 'Article publié');
        $this->assertSelectorTextNotContains('body', 'Article brouillon');
    }

    public function testGuestsCanReadPublishedArticle(): void
    {
        $author = $this->createUser('author2@example.com', 'Auteur', 'password123');
        $post = $this->createBlogPost($author, 'Lecture publique', 'Le contenu de l’article.', true);

        $this->client->request('GET', '/ressources/blog/'.$post->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Lecture publique');
        $this->assertSelectorTextContains('body', 'Le contenu de l’article.');
    }

    public function testGuestsCannotReadUnpublishedArticle(): void
    {
        $author = $this->createUser('author3@example.com', 'Auteur', 'password123');
        $post = $this->createBlogPost($author, 'Confidentiel', 'Article caché', false);

        $this->client->request('GET', '/ressources/blog/'.$post->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAdminCanCreateEditAndDeleteAnArticle(): void
    {
        $admin = $this->createUser('admin@example.com', 'Admin', 'password123', true);
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/ressources/blog/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer l\'article')->form([
            'blog_post[title]' => 'Nouvel article',
            'blog_post[content]' => 'Contenu initial',
            'blog_post[isPublished]' => '1',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/ressources');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Nouvel article');

        /** @var BlogPost $post */
        $post = $this->em->getRepository(BlogPost::class)->findOneBy(['title' => 'Nouvel article']);
        $this->assertInstanceOf(BlogPost::class, $post);
        $this->assertSame($admin->getId(), $post->getAuthor()?->getId());
        $this->assertNotNull($post->getUpdatedAt());

        $crawler = $this->client->request('GET', '/ressources/blog/'.$post->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $editForm = $crawler->selectButton('Enregistrer les modifications')->form([
            'blog_post[title]' => 'Article mis à jour',
            'blog_post[content]' => 'Contenu modifié',
            'blog_post[isPublished]' => '1',
        ]);

        $this->client->submit($editForm);
        $this->assertResponseRedirects('/ressources/blog/'.$post->getId());

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Article mis à jour');
        $this->assertSelectorTextContains('body', 'Contenu modifié');

        $crawler = $this->client->request('GET', '/ressources/blog/'.$post->getId());
        $deleteForm = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/ressources');
        $this->em->clear();
        $this->assertNull($this->em->getRepository(BlogPost::class)->find($post->getId()));
    }

    public function testRegularUserCannotCreateAnArticle(): void
    {
        $user = $this->createUser('user@example.com', 'Utilisateur', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/ressources/blog/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanReadUnpublishedArticle(): void
    {
        $admin = $this->createUser('admin-read@example.com', 'Admin', 'password123', true);
        $post = $this->createBlogPost($admin, 'Brouillon admin', 'Contenu privé', false);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/ressources/blog/'.$post->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Brouillon admin');
        $this->assertSelectorTextContains('body', 'Contenu privé');
    }
}
