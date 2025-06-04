<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/cours')]
class AdminCoursController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_cours_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $cours = $this->entityManager->getRepository(Cours::class)->findAll();

        return $this->render('admin/cours/index.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/new', name: 'admin_cours_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $cours = new Cours();
        $form = $this->createForm(CoursFormType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($cours);
            $this->entityManager->flush();

            $this->addFlash('success', 'Cours créé avec succès.');

            return $this->redirectToRoute('admin_cours_index');
        }

        return $this->render('admin/cours/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_cours_edit')]
    public function edit(Request $request, Cours $cours): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CoursFormType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Cours mis à jour avec succès.');

            return $this->redirectToRoute('admin_cours_index');
        }

        return $this->render('admin/cours/edit.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cours): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$cours->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($cours);
            $this->entityManager->flush();

            $this->addFlash('success', 'Cours supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_cours_index');
    }
} 