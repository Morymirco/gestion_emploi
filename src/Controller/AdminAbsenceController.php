<?php

namespace App\Controller;

use App\Entity\Absence;
use App\Form\AbsenceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/absence')]
class AdminAbsenceController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_absence_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Filtres
        $statut = $request->query->get('statut');
        $type = $request->query->get('type');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $queryBuilder = $this->entityManager->getRepository(Absence::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.utilisateur', 'u')
            ->leftJoin('a.emploiDuTemps', 'e')
            ->leftJoin('e.cours', 'c')
            ->orderBy('a.dateAbsence', 'DESC');

        if ($statut) {
            $queryBuilder->andWhere('a.statut = :statut')
                        ->setParameter('statut', $statut);
        }

        if ($type) {
            $queryBuilder->andWhere('a.type = :type')
                        ->setParameter('type', $type);
        }

        if ($dateDebut) {
            $queryBuilder->andWhere('a.dateAbsence >= :dateDebut')
                        ->setParameter('dateDebut', new \DateTime($dateDebut));
        }

        if ($dateFin) {
            $queryBuilder->andWhere('a.dateAbsence <= :dateFin')
                        ->setParameter('dateFin', new \DateTime($dateFin));
        }

        $absences = $queryBuilder->getQuery()->getResult();

        $stats = $this->getAbsenceStats();

        return $this->render('admin/absence/index.html.twig', [
            'absences' => $absences,
            'stats' => $stats,
            'filtres' => [
                'statut' => $statut,
                'type' => $type,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]
        ]);
    }

    #[Route('/new', name: 'admin_absence_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $absence = new Absence();
        $absence->setDeclarePar($this->getUser());
        
        $form = $this->createForm(AbsenceFormType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($absence);
            $this->entityManager->flush();

            $this->addFlash('success', 'Absence déclarée avec succès.');

            return $this->redirectToRoute('admin_absence_index');
        }

        return $this->render('admin/absence/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_absence_edit')]
    public function edit(Request $request, Absence $absence): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AbsenceFormType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Absence mise à jour avec succès.');

            return $this->redirectToRoute('admin_absence_index');
        }

        return $this->render('admin/absence/edit.html.twig', [
            'form' => $form->createView(),
            'absence' => $absence,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_absence_delete', methods: ['POST'])]
    public function delete(Request $request, Absence $absence): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$absence->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($absence);
            $this->entityManager->flush();

            $this->addFlash('success', 'Absence supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_absence_index');
    }

    #[Route('/{id}/approve', name: 'admin_absence_approve', methods: ['POST'])]
    public function approve(Absence $absence): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $absence->setStatut('approuvee');
        $this->entityManager->flush();

        $this->addFlash('success', 'Absence approuvée avec succès.');

        return $this->redirectToRoute('admin_absence_index');
    }

    #[Route('/{id}/reject', name: 'admin_absence_reject', methods: ['POST'])]
    public function reject(Absence $absence): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $absence->setStatut('rejetee');
        $this->entityManager->flush();

        $this->addFlash('success', 'Absence rejetée avec succès.');

        return $this->redirectToRoute('admin_absence_index');
    }

    #[Route('/statistiques', name: 'admin_absence_stats')]
    public function statistiques(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $this->getDetailedStats();

        return $this->render('admin/absence/statistiques.html.twig', [
            'stats' => $stats
        ]);
    }

    private function getAbsenceStats(): array
    {
        $total = $this->entityManager->getRepository(Absence::class)->count([]);
        $enAttente = $this->entityManager->getRepository(Absence::class)->count(['statut' => 'en_attente']);
        $approuvees = $this->entityManager->getRepository(Absence::class)->count(['statut' => 'approuvee']);
        $rejetees = $this->entityManager->getRepository(Absence::class)->count(['statut' => 'rejetee']);

        $etudiant = $this->entityManager->getRepository(Absence::class)->count(['type' => 'etudiant']);
        $enseignant = $this->entityManager->getRepository(Absence::class)->count(['type' => 'enseignant']);

        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'approuvees' => $approuvees,
            'rejetees' => $rejetees,
            'etudiant' => $etudiant,
            'enseignant' => $enseignant
        ];
    }

    private function getDetailedStats(): array
    {
        // Absences par mois
        $absencesParMois = $this->entityManager->getRepository(Absence::class)
            ->createQueryBuilder('a')
            ->select('MONTH(a.dateAbsence) as mois, COUNT(a.id) as nb_absences')
            ->where('a.dateAbsence >= :debut')
            ->setParameter('debut', new \DateTime('-12 months'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->getQuery()
            ->getResult();

        // Top utilisateurs avec absences
        $topAbsents = $this->entityManager->getRepository(Absence::class)
            ->createQueryBuilder('a')
            ->select('u.prenom, u.nom, COUNT(a.id) as nb_absences')
            ->leftJoin('a.utilisateur', 'u')
            ->groupBy('u.id')
            ->orderBy('nb_absences', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return [
            'basic' => $this->getAbsenceStats(),
            'par_mois' => $absencesParMois,
            'top_absents' => $topAbsents
        ];
    }
} 