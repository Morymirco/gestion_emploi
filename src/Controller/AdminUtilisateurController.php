<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/utilisateur')]
class AdminUtilisateurController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/', name: 'admin_utilisateur_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Filtres
        $roleFilter = $request->query->get('role');
        $departementFilter = $request->query->get('departement');
        $niveauFilter = $request->query->get('niveau');
        $searchFilter = $request->query->get('search');

        $queryBuilder = $this->entityManager->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.departement', 'd')
            ->leftJoin('u.niveau', 'n')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC');

        if ($roleFilter) {
            $queryBuilder->andWhere('u.role = :role')
                        ->setParameter('role', $roleFilter);
        }

        if ($departementFilter) {
            $queryBuilder->andWhere('u.departement = :departement')
                        ->setParameter('departement', $departementFilter);
        }

        if ($niveauFilter) {
            $queryBuilder->andWhere('u.niveau = :niveau')
                        ->setParameter('niveau', $niveauFilter);
        }

        if ($searchFilter) {
            $queryBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                        ->setParameter('search', '%' . $searchFilter . '%');
        }

        $utilisateurs = $queryBuilder->getQuery()->getResult();

        // Données pour les filtres
        $departements = $this->entityManager->getRepository(\App\Entity\Departement::class)->findAll();
        $niveaux = $this->entityManager->getRepository(\App\Entity\Niveau::class)->findAll();

        // Statistiques
        $stats = $this->getUserStats();

        return $this->render('admin/utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'departements' => $departements,
            'niveaux' => $niveaux,
            'stats' => $stats,
            'filtres' => [
                'role' => $roleFilter,
                'departement' => $departementFilter,
                'niveau' => $niveauFilter,
                'search' => $searchFilter,
            ]
        ]);
    }

    #[Route('/new', name: 'admin_utilisateur_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $utilisateur = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('motDePasse')->getData();
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setMotDePasse($hashedPassword);

            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('admin_utilisateur_index');
        }

        return $this->render('admin/utilisateur/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_utilisateur_edit')]
    public function edit(Request $request, Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(RegistrationFormType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('motDePasse')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setMotDePasse($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('admin_utilisateur_index');
        }

        return $this->render('admin/utilisateur/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($utilisateur);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_utilisateur_index');
    }

    #[Route('/{id}/profile', name: 'admin_utilisateur_profile')]
    public function profile(Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer les cours de l'utilisateur selon son rôle
        $cours = [];
        $emploisDuTemps = [];
        $absences = [];

        if ($utilisateur->getRole()->value === 'enseignant') {
            $cours = $utilisateur->getCours();
            $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
                ->createQueryBuilder('e')
                ->leftJoin('e.cours', 'c')
                ->where('c.enseignant = :enseignant')
                ->setParameter('enseignant', $utilisateur)
                ->orderBy('e.date', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        } elseif ($utilisateur->getRole()->value === 'etudiant' && $utilisateur->getNiveau()) {
            $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
                ->createQueryBuilder('e')
                ->where('e.niveau = :niveau')
                ->setParameter('niveau', $utilisateur->getNiveau())
                ->orderBy('e.date', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }

        $absences = $utilisateur->getAbsences();

        return $this->render('admin/utilisateur/profile.html.twig', [
            'utilisateur' => $utilisateur,
            'cours' => $cours,
            'emploisDuTemps' => $emploisDuTemps,
            'absences' => $absences,
        ]);
    }

    private function getUserStats(): array
    {
        $totalUsers = $this->entityManager->getRepository(Utilisateur::class)->count([]);
        $admins = $this->entityManager->getRepository(Utilisateur::class)->count(['role' => 'admin']);
        $enseignants = $this->entityManager->getRepository(Utilisateur::class)->count(['role' => 'enseignant']);
        $etudiants = $this->entityManager->getRepository(Utilisateur::class)->count(['role' => 'etudiant']);

        return [
            'total' => $totalUsers,
            'admins' => $admins,
            'enseignants' => $enseignants,
            'etudiants' => $etudiants
        ];
    }
}