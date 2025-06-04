<?php

namespace App\Controller;

use App\Entity\EmploiDuTemps;
use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Departement;
use App\Entity\Niveau;
use App\Form\EmploiDuTempsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/emploi-du-temps')]
class AdminEmploiDuTempsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_emploi_du_temps_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupération des filtres
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $departementId = $request->query->get('departement');
        $niveauId = $request->query->get('niveau');

        // Construction de la requête avec filtres
        $queryBuilder = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cours', 'c')
            ->leftJoin('e.salle', 's')
            ->leftJoin('e.departement', 'd')
            ->leftJoin('e.niveau', 'n')
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC');

        if ($dateDebut) {
            $queryBuilder->andWhere('e.date >= :dateDebut')
                        ->setParameter('dateDebut', new \DateTime($dateDebut));
        }

        if ($dateFin) {
            $queryBuilder->andWhere('e.date <= :dateFin')
                        ->setParameter('dateFin', new \DateTime($dateFin));
        }

        if ($departementId) {
            $queryBuilder->andWhere('e.departement = :departement')
                        ->setParameter('departement', $departementId);
        }

        if ($niveauId) {
            $queryBuilder->andWhere('e.niveau = :niveau')
                        ->setParameter('niveau', $niveauId);
        }

        $emploisDuTemps = $queryBuilder->getQuery()->getResult();

        // Données pour les filtres
        $departements = $this->entityManager->getRepository(Departement::class)->findAll();
        $niveaux = $this->entityManager->getRepository(Niveau::class)->findAll();

        return $this->render('admin/emploi_du_temps/index.html.twig', [
            'emploisDuTemps' => $emploisDuTemps,
            'departements' => $departements,
            'niveaux' => $niveaux,
            'filtres' => [
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'departement' => $departementId,
                'niveau' => $niveauId,
            ]
        ]);
    }

    #[Route('/calendrier', name: 'admin_emploi_du_temps_calendrier')]
    public function calendrier(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $semaine = $request->query->get('semaine', date('Y-m-d'));
        $dateDebut = new \DateTime($semaine);
        $dateDebut->modify('monday this week');
        
        $dateFin = clone $dateDebut;
        $dateFin->modify('+6 days');

        $emploisDuTemps = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cours', 'c')
            ->leftJoin('e.salle', 's')
            ->leftJoin('e.departement', 'd')
            ->leftJoin('e.niveau', 'n')
            ->where('e.date BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        // Organisation par jour et heure
        $planning = [];
        $heures = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        foreach ($jours as $jour) {
            $planning[$jour] = [];
            foreach ($heures as $heure) {
                $planning[$jour][$heure] = [];
            }
        }

        foreach ($emploisDuTemps as $emploi) {
            $jourSemaine = $this->getJourSemaine($emploi->getDate());
            $heure = $emploi->getHeureDebut()->format('H:i');
            if (isset($planning[$jourSemaine][$heure])) {
                $planning[$jourSemaine][$heure][] = $emploi;
            }
        }

        return $this->render('admin/emploi_du_temps/calendrier.html.twig', [
            'planning' => $planning,
            'semaine' => $dateDebut,
            'semaineFormatted' => $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y'),
            'heures' => $heures,
            'jours' => $jours
        ]);
    }

    #[Route('/analyse', name: 'admin_emploi_du_temps_analyse')]
    public function analyse(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Statistiques générales
        $totalEmplois = $this->entityManager->getRepository(EmploiDuTemps::class)->count([]);
        $totalCours = $this->entityManager->getRepository(Cours::class)->count([]);
        $totalSalles = $this->entityManager->getRepository(Salle::class)->count([]);

        // Occupation des salles
        $occupationSalles = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->select('s.nomSalle, COUNT(e.id) as nb_utilisations')
            ->leftJoin('e.salle', 's')
            ->groupBy('s.id')
            ->orderBy('nb_utilisations', 'DESC')
            ->getQuery()
            ->getResult();

        // Répartition par département
        $repartitionDepartements = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->select('d.nomDepartement, COUNT(e.id) as nb_cours')
            ->leftJoin('e.departement', 'd')
            ->groupBy('d.id')
            ->orderBy('nb_cours', 'DESC')
            ->getQuery()
            ->getResult();

        // Répartition par niveau
        $repartitionNiveaux = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->select('n.nomNiveau, COUNT(e.id) as nb_cours')
            ->leftJoin('e.niveau', 'n')
            ->groupBy('n.id')
            ->orderBy('nb_cours', 'DESC')
            ->getQuery()
            ->getResult();

        // Détection de conflits (même salle, même heure)
        $conflits = $this->entityManager->getRepository(EmploiDuTemps::class)
            ->createQueryBuilder('e1')
            ->select('e1, e2, s.nomSalle')
            ->join('App\Entity\EmploiDuTemps', 'e2', 'WITH', 
                'e1.salle = e2.salle AND e1.date = e2.date AND e1.id != e2.id AND '.
                '((e1.heureDebut <= e2.heureDebut AND e1.heureFin > e2.heureDebut) OR '.
                '(e2.heureDebut <= e1.heureDebut AND e2.heureFin > e1.heureDebut))')
            ->leftJoin('e1.salle', 's')
            ->getQuery()
            ->getResult();

        return $this->render('admin/emploi_du_temps/analyse.html.twig', [
            'stats' => [
                'total_emplois' => $totalEmplois,
                'total_cours' => $totalCours,
                'total_salles' => $totalSalles,
            ],
            'occupation_salles' => $occupationSalles,
            'repartition_departements' => $repartitionDepartements,
            'repartition_niveaux' => $repartitionNiveaux,
            'conflits' => $conflits
        ]);
    }

    #[Route('/new', name: 'admin_emploi_du_temps_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $emploiDuTemps = new EmploiDuTemps();
        $form = $this->createForm(EmploiDuTempsFormType::class, $emploiDuTemps);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calcul automatique de la durée
            $debut = $emploiDuTemps->getHeureDebut();
            $fin = $emploiDuTemps->getHeureFin();
            if ($debut && $fin) {
                $duree = $debut->diff($fin);
                $emploiDuTemps->setDurée($duree->format('%H:%I'));
            }

            $this->entityManager->persist($emploiDuTemps);
            $this->entityManager->flush();

            $this->addFlash('success', 'Emploi du temps créé avec succès.');

            return $this->redirectToRoute('admin_emploi_du_temps_index');
        }

        return $this->render('admin/emploi_du_temps/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_emploi_du_temps_edit')]
    public function edit(Request $request, EmploiDuTemps $emploiDuTemps): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(EmploiDuTempsFormType::class, $emploiDuTemps);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Recalcul de la durée
            $debut = $emploiDuTemps->getHeureDebut();
            $fin = $emploiDuTemps->getHeureFin();
            if ($debut && $fin) {
                $duree = $debut->diff($fin);
                $emploiDuTemps->setDurée($duree->format('%H:%I'));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Emploi du temps mis à jour avec succès.');

            return $this->redirectToRoute('admin_emploi_du_temps_index');
        }

        return $this->render('admin/emploi_du_temps/edit.html.twig', [
            'form' => $form->createView(),
            'emploiDuTemps' => $emploiDuTemps,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_emploi_du_temps_delete', methods: ['POST'])]
    public function delete(Request $request, EmploiDuTemps $emploiDuTemps): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$emploiDuTemps->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($emploiDuTemps);
            $this->entityManager->flush();

            $this->addFlash('success', 'Emploi du temps supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_emploi_du_temps_index');
    }

    private function getJourSemaine(\DateTimeInterface $date): string
    {
        $jours = [
            1 => 'Lundi',
            2 => 'Mardi', 
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];
        
        return $jours[(int)$date->format('N')];
    }
} 