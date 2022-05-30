<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("ROLE_ADMIN")]
class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_user_list')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/user.list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, UserRepository $userRepository, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $doctrine->getManager();
            $manager->flush();
            
            return $this->redirect($this->generateUrl("admin_user_list"));
        }
        return $this->render('admin/user.edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete')]
    public function delete(ManagerRegistry $doctrine, User $user): Response
    {
        $manager = $doctrine->getManager();
        $manager->remove($user);
        $manager->flush();

        return $this->redirect($this->generateUrl("admin_user_list"));
    }
}
