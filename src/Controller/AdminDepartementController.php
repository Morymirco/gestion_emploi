<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Form\DepartementFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/departement')]
class AdminDepartementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_departement_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $départements = $this->entityManager->getRepository(Departement::class)->findAll();

        return $this->render('admin/departement/index.html.twig', [
            'départements' => $départements,
        ]);
    }

    #[Route('/new', name: 'admin_departement_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $département = new Departement();
        $form = $this->createForm(DepartementFormType::class, $département);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($département);
            $this->entityManager->flush();

            $this->addFlash('success', 'Département créé avec succès.');

            return $this->redirectToRoute('admin_departement_index');
        }

        return $this->render('admin/departement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_departement_edit')]
    public function edit(Request $request, Departement $département): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(DepartementFormType::class, $département);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Département mis à jour avec succès.');

            return $this->redirectToRoute('admin_departement_index');
        }

        return $this->render('admin/departement/edit.html.twig', [
            'form' => $form->createView(),
            'département' => $département,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_departement_delete', methods: ['POST'])]
    public function delete(Request $request, Departement $département): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$département->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($département);
            $this->entityManager->flush();

            $this->addFlash('success', 'Département supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_departement_index');
    }
} 