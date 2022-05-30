<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @method User|null getUser()
 */
class CommentController extends AbstractController
{
    #[Route('/posts/{id}/comments', name: 'comment_create', methods: ["POST"])]
    public function create(Request $request, ManagerRegistry $doctrine, Post $post): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $doctrine->getManager();
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());

            $user = $this->getUser();
            if (!$user && !$comment->getUsername()) {
                $this->addFlash("danger", "Le pseudo est requis.");
            } else {
                if ($user) $comment->setAuthor($user);
                $manager->persist($comment);
                $manager->flush();
            }
        }

        return $this->redirect($this->generateUrl("post_detail", ["id" => $post->getId()]));
    }
    #[Route('/comments/{id}/delete', name: 'comment_delete', methods: ["GET"])]
    #[IsGranted('ROLE_USER')]
    public function delete(ManagerRegistry $doctrine, Comment $comment): Response
    {
        $user = $this->getUser();
        if (
            $this->isGranted("ROLE_ADMIN") ||
            ($this->isGranted("ROLE_AUTHOR") && $comment->getPost()->getAuthor()->getId() === $user->getId()) ||
            $comment->getAuthor()->getId() === $user->getId()
        ) {
            $manager = $doctrine->getManager();
            $manager->remove($comment);
            $manager->flush();
        }
        
        return $this->redirect($this->generateUrl("post_detail", ["id" => $comment->getPost()->getId()]));
    }
}
