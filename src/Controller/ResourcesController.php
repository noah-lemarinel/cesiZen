<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\User;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ressources')]
class ResourcesController extends AbstractController
{
    #[Route('', name: 'resources_index')]
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        $posts = $blogPostRepository->findPublished();

        return $this->render('resources/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/blog/new', name: 'blog_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Check if user is admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('Only admins can create blog posts.');
        }

        $post = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($currentUser);
            $post->setUpdatedAt(new \DateTime());
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Blog post created successfully!');

            return $this->redirectToRoute('resources_index');
        }

        return $this->render('resources/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/blog/{id}', name: 'blog_post_show')]
    public function show(BlogPost $post): Response
    {
        $currentUser = $this->getUser();
        if (!$post->isPublished() && (!$currentUser instanceof User || !$currentUser->isAdmin())) {
            throw $this->createNotFoundException('Ce blog n\'existe pas.');
        }

        return $this->render('resources/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/blog/{id}/edit', name: 'blog_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(BlogPost $post, Request $request, EntityManagerInterface $em): Response
    {
        // Check if user is admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('Only admins can edit blog posts.');
        }

        $form = $this->createForm(BlogPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Blog post updated successfully!');

            return $this->redirectToRoute('blog_post_show', ['id' => $post->getId()]);
        }

        return $this->render('resources/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/blog/{id}/delete', name: 'blog_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(BlogPost $post, Request $request, EntityManagerInterface $em): Response
    {
        // Check if user is admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('Only admins can delete blog posts.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();

            $this->addFlash('success', 'Blog post deleted successfully!');
        }

        return $this->redirectToRoute('resources_index');
    }
}
