<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $doctrine->getManager();
            $manager->persist($contact);
            $manager->flush();
            $this->addFlash("success", "Le message a bien été envoyé.");
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/contact', name: 'admin_contact_list')]
    #[IsGranted("ROLE_ADMIN")]
    public function adminList(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();
        return $this->render('/admin/contact.html.twig', [
            'contacts' => $contacts
        ]);
    }


    #[Route('/admin/contact/{id}/delete', name: 'admin_contact_delete')]
    #[IsGranted("ROLE_ADMIN")]
    public function deletePost(ManagerRegistry $doctrine, Contact $contact): Response
    {
        $manager = $doctrine->getManager();
        $manager->remove($contact);
        $manager->flush();

        return $this->redirect($this->generateUrl("admin_contact_list"));
    }
}
