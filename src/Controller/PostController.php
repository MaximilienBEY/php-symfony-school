<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method User|null getUser()
 */
class PostController extends AbstractController
{
    #[Route('/posts', name: 'posts')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/posts/{id}', name: 'post_detail')]
    public function detail(PostRepository $postRepository, Post $post): Response
    {
        $lastPosts = $postRepository->findBy([], ["id" => "DESC"], 3, 0);
        $form = $this->createForm(CommentType::class, null, ["action" => $this->generateUrl("comment_create", ["id" => $post->getId()])]);

        return $this->render('post/detail.html.twig', [
            'post' => $post,
            'last_posts' => $lastPosts,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin', name: 'admin')]
    #[Route('/author/posts', name: 'author')]
    #[IsGranted("ROLE_AUTHOR")]
    public function adminList(PostRepository $postRepository): Response
    {
        $user = $this->getUser();
        $posts = $this->isGranted("ROLE_ADMIN") ? $postRepository->findAll() : $postRepository->findBy(["author" => $user]);
        return $this->render('/admin/index.html.twig', [
            'posts' => $posts
        ]);
    }
    #[Route('/admin/posts/create', name: 'admin_post_create')]
    #[Route('/author/posts/create', name: 'author_post_create')]
    #[IsGranted("ROLE_AUTHOR")]
    public function createPost(Request $request, ManagerRegistry $doctrine): Response
    {
        $post = new Post();
        $user = $this->getUser();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $doctrine->getManager();
            if (!$this->isGranted("ROLE_ADMIN")) $post->setAuthor($user);
            $manager->persist($post);
            $manager->flush();
            return $this->redirect($this->generateUrl("admin"));
        } else if ($form->isSubmitted()) {
            $errors = array();
            foreach ($form as $formField) {
                $temp = array_filter(explode("\n", $formField->getErrors()->__toString()), fn (string $str) => !!strlen($str));
                $temp = array_map(fn (string $str) => str_replace("ERROR: ", "", $str), $temp);

                $errors = array_merge($errors, $temp);
            }
            array_map(fn (string $str) => $this->addFlash("danger", $str), $errors);
        }
    
        return $this->render('/admin/post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/posts/{id}/edit', name: 'admin_post_edit')]
    #[Route('/author/posts/{id}/edit', name: 'author_post_edit')]
    #[IsGranted("ROLE_AUTHOR")]
    public function editPost(Request $request, ManagerRegistry $doctrine, Post $post): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $doctrine->getManager();
            if (!$this->isGranted("ROLE_ADMIN")) {
                $post->setAuthor($user);
                $manager->flush();
                return $this->redirect($this->generateUrl("author"));
            } else {
                $manager->flush();
                return $this->redirect($this->generateUrl("admin"));
            }
        } else if ($form->isSubmitted()) {
            $errors = array();
            foreach ($form as $formField) {
                $temp = array_filter(explode("\n", $formField->getErrors()->__toString()), fn (string $str) => !!strlen($str));
                $temp = array_map(fn (string $str) => str_replace("ERROR: ", "", $str), $temp);

                $errors = array_merge($errors, $temp);
            }
            array_map(fn (string $str) => $this->addFlash("danger", $str), $errors);
        }
    
        return $this->render('/admin/post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/posts/{id}/delete', name: 'admin_post_delete')]
    #[Route('/author/posts/{id}/delete', name: 'author_post_delete')]
    #[IsGranted("ROLE_AUTHOR")]
    public function deletePost(ManagerRegistry $doctrine, Post $post): Response
    {
        $user = $this->getUser();
        if (!$this->isGranted("ROLE_ADMIN") && $user->getId() !== $post->getAuthor()->getId()) {
            return $this->redirect($this->generateUrl("author"));
        }
        $manager = $doctrine->getManager();
        $manager->remove($post);
        $manager->flush();

        return $this->redirect($this->generateUrl("admin"));
    }
}
