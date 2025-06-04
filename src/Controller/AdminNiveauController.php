<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Form\NiveauFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/niveau')]
class AdminNiveauController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_niveau_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $niveaux = $this->entityManager->getRepository(Niveau::class)->findAll();

        return $this->render('admin/niveau/index.html.twig', [
            'niveaux' => $niveaux,
        ]);
    }

    #[Route('/new', name: 'admin_niveau_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $niveau = new Niveau();
        $form = $this->createForm(NiveauFormType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($niveau);
            $this->entityManager->flush();

            $this->addFlash('success', 'Niveau créé avec succès.');

            return $this->redirectToRoute('admin_niveau_index');
        }

        return $this->render('admin/niveau/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_niveau_edit')]
    public function edit(Request $request, Niveau $niveau): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(NiveauFormType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Niveau mis à jour avec succès.');

            return $this->redirectToRoute('admin_niveau_index');
        }

        return $this->render('admin/niveau/edit.html.twig', [
            'form' => $form->createView(),
            'niveau' => $niveau,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_niveau_delete', methods: ['POST'])]
    public function delete(Request $request, Niveau $niveau): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$niveau->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($niveau);
            $this->entityManager->flush();

            $this->addFlash('success', 'Niveau supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_niveau_index');
    }
} 