<?php

namespace App\Controller;

use App\Entity\Creneau;
use App\Form\CreneauFormType;
use App\Enum\TypeCreneau;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/creneau')]
class AdminCreneauController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_creneau_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Filtres
        $jourFilter = $request->query->get('jour');
        $typeFilter = $request->query->get('type');
        $salleFilter = $request->query->get('salle');

        $queryBuilder = $this->entityManager->getRepository(Creneau::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.cours', 'cours')
            ->leftJoin('c.salle', 's')
            ->leftJoin('c.niveau', 'n')
            ->leftJoin('c.module', 'm')
            ->leftJoin('c.enseignant', 'e')
            ->orderBy('c.jourSemaine', 'ASC')
            ->addOrderBy('c.heureDebut', 'ASC');

        if ($jourFilter) {
            $queryBuilder->andWhere('c.jourSemaine = :jour')
                        ->setParameter('jour', $jourFilter);
        }

        if ($typeFilter) {
            $queryBuilder->andWhere('c.typeCreneau = :type')
                        ->setParameter('type', TypeCreneau::from($typeFilter));
        }

        if ($salleFilter) {
            $queryBuilder->andWhere('s.id = :salle')
                        ->setParameter('salle', $salleFilter);
        }

        $creneaux = $queryBuilder->getQuery()->getResult();

        // Détecter les conflits
        $conflits = $this->detecterConflits($creneaux);

        $stats = $this->getCreneauStats();

        return $this->render('admin/creneau/index.html.twig', [
            'creneaux' => $creneaux,
            'conflits' => $conflits,
            'stats' => $stats,
            'filtres' => [
                'jour' => $jourFilter,
                'type' => $typeFilter,
                'salle' => $salleFilter,
            ]
        ]);
    }

    #[Route('/new', name: 'admin_creneau_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $creneau = new Creneau();
        $form = $this->createForm(CreneauFormType::class, $creneau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier les conflits avant de sauvegarder
            $conflits = $this->verifierConflits($creneau);
            
            if (!empty($conflits)) {
                foreach ($conflits as $conflit) {
                    $this->addFlash('error', "Conflit détecté avec : {$conflit}");
                }
            } else {
                $this->entityManager->persist($creneau);
                $this->entityManager->flush();

                $this->addFlash('success', 'Créneau créé avec succès.');
                return $this->redirectToRoute('admin_creneau_index');
            }
        }

        return $this->render('admin/creneau/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_creneau_edit')]
    public function edit(Request $request, Creneau $creneau): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CreneauFormType::class, $creneau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier les conflits avant de sauvegarder (exclure le créneau actuel)
            $conflits = $this->verifierConflits($creneau, $creneau->getId());
            
            if (!empty($conflits)) {
                foreach ($conflits as $conflit) {
                    $this->addFlash('error', "Conflit détecté avec : {$conflit}");
                }
            } else {
                $this->entityManager->flush();

                $this->addFlash('success', 'Créneau mis à jour avec succès.');
                return $this->redirectToRoute('admin_creneau_index');
            }
        }

        return $this->render('admin/creneau/edit.html.twig', [
            'form' => $form->createView(),
            'creneau' => $creneau,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_creneau_delete', methods: ['POST'])]
    public function delete(Request $request, Creneau $creneau): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$creneau->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($creneau);
            $this->entityManager->flush();

            $this->addFlash('success', 'Créneau supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_creneau_index');
    }

    #[Route('/calendrier', name: 'admin_creneau_calendrier')]
    public function calendrier(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les créneaux groupés par jour
        $creneauxParJour = [];
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

        foreach ($jours as $jour) {
            $creneauxParJour[$jour] = $this->entityManager->getRepository(Creneau::class)
                ->createQueryBuilder('c')
                ->where('c.jourSemaine = :jour')
                ->andWhere('c.actif = true')
                ->setParameter('jour', $jour)
                ->orderBy('c.heureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Générer les plages horaires (de 8h à 18h)
        $heures = [];
        for ($h = 8; $h <= 18; $h++) {
            $heures[] = sprintf('%02d:00', $h);
        }

        return $this->render('admin/creneau/calendrier.html.twig', [
            'creneauxParJour' => $creneauxParJour,
            'jours' => $jours,
            'heures' => $heures
        ]);
    }

    #[Route('/calendrier/pdf', name: 'admin_creneau_calendrier_pdf')]
    public function calendrierPdf(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les créneaux groupés par jour
        $creneauxParJour = [];
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

        foreach ($jours as $jour) {
            $creneauxParJour[$jour] = $this->entityManager->getRepository(Creneau::class)
                ->createQueryBuilder('c')
                ->where('c.jourSemaine = :jour')
                ->andWhere('c.actif = true')
                ->setParameter('jour', $jour)
                ->orderBy('c.heureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Générer les plages horaires (de 8h à 18h)
        $heures = [];
        for ($h = 8; $h <= 18; $h++) {
            $heures[] = sprintf('%02d:00', $h);
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('admin/creneau/calendrier_pdf.html.twig', [
            'creneauxParJour' => $creneauxParJour,
            'jours' => $jours,
            'heures' => $heures
        ]);

        // Configurer DomPDF
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Configuration du papier - A4 paysage pour mieux afficher le calendrier
        $dompdf->setPaper('A4', 'landscape');
        
        // Générer le PDF
        $dompdf->render();
        
        // Nom du fichier avec la date
        $filename = 'emploi_du_temps_' . date('Y-m-d_H-i') . '.pdf';
        
        // Retourner le PDF
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }

    #[Route('/calendrier/pdf-avance', name: 'admin_creneau_calendrier_pdf_avance')]
    public function calendrierPdfAvance(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Paramètres d'export
        $orientation = $request->query->get('orientation', 'landscape'); // landscape ou portrait
        $includeStats = $request->query->get('stats', '1') === '1';
        $includeLegend = $request->query->get('legend', '1') === '1';
        $jourFilter = $request->query->get('jour');

        // Récupérer tous les créneaux groupés par jour
        $creneauxParJour = [];
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

        // Si un filtre jour est appliqué, on ne prend que ce jour
        if ($jourFilter && in_array($jourFilter, $jours)) {
            $jours = [$jourFilter];
        }

        foreach ($jours as $jour) {
            $creneauxParJour[$jour] = $this->entityManager->getRepository(Creneau::class)
                ->createQueryBuilder('c')
                ->where('c.jourSemaine = :jour')
                ->andWhere('c.actif = true')
                ->setParameter('jour', $jour)
                ->orderBy('c.heureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Générer les plages horaires (de 8h à 18h)
        $heures = [];
        for ($h = 8; $h <= 18; $h++) {
            $heures[] = sprintf('%02d:00', $h);
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('admin/creneau/calendrier_pdf.html.twig', [
            'creneauxParJour' => $creneauxParJour,
            'jours' => $jours,
            'heures' => $heures,
            'includeStats' => $includeStats,
            'includeLegend' => $includeLegend,
            'singleJour' => $jourFilter ? true : false
        ]);

        // Configurer DomPDF
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Configuration du papier
        $dompdf->setPaper('A4', $orientation);
        
        // Générer le PDF
        $dompdf->render();
        
        // Nom du fichier avec la date et filtres
        $filename = 'emploi_du_temps';
        if ($jourFilter) {
            $filename .= '_' . $jourFilter;
        }
        $filename .= '_' . date('Y-m-d_H-i') . '.pdf';
        
        // Retourner le PDF
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }

    private function verifierConflits(Creneau $nouveauCreneau, ?int $excludeId = null): array
    {
        $conflits = [];

        // Récupérer les créneaux du même jour
        $queryBuilder = $this->entityManager->getRepository(Creneau::class)
            ->createQueryBuilder('c')
            ->where('c.jourSemaine = :jour')
            ->andWhere('c.actif = true')
            ->setParameter('jour', $nouveauCreneau->getJourSemaine());

        if ($excludeId) {
            $queryBuilder->andWhere('c.id != :excludeId')
                        ->setParameter('excludeId', $excludeId);
        }

        $creneauxExistants = $queryBuilder->getQuery()->getResult();

        foreach ($creneauxExistants as $creneau) {
            if ($nouveauCreneau->hasConflictWith($creneau)) {
                // Vérifier les conflits de salle
                if ($nouveauCreneau->getSalle() && $creneau->getSalle() && 
                    $nouveauCreneau->getSalle()->getId() === $creneau->getSalle()->getId()) {
                    $conflits[] = "Salle {$creneau->getSalle()->getNomSalle()} déjà occupée le {$creneau->getJourSemaine()} de {$creneau->getDuree()}";
                }

                // Vérifier les conflits d'enseignant
                if ($nouveauCreneau->getEnseignant() && $creneau->getEnseignant() && 
                    $nouveauCreneau->getEnseignant()->getId() === $creneau->getEnseignant()->getId()) {
                    $conflits[] = "Enseignant {$creneau->getEnseignant()->getPrenom()} {$creneau->getEnseignant()->getNom()} déjà programmé le {$creneau->getJourSemaine()} de {$creneau->getDuree()}";
                }

                // Vérifier les conflits de niveau
                if ($nouveauCreneau->getNiveau() && $creneau->getNiveau() && 
                    $nouveauCreneau->getNiveau()->getId() === $creneau->getNiveau()->getId()) {
                    $conflits[] = "Niveau {$creneau->getNiveau()->getNomNiveau()} déjà en cours le {$creneau->getJourSemaine()} de {$creneau->getDuree()}";
                }
            }
        }

        return $conflits;
    }

    private function detecterConflits(array $creneaux): array
    {
        $conflits = [];
        
        for ($i = 0; $i < count($creneaux); $i++) {
            for ($j = $i + 1; $j < count($creneaux); $j++) {
                $creneau1 = $creneaux[$i];
                $creneau2 = $creneaux[$j];
                
                if ($creneau1->hasConflictWith($creneau2)) {
                    $conflit = [
                        'creneau1' => $creneau1,
                        'creneau2' => $creneau2,
                        'type' => $this->getTypeConflit($creneau1, $creneau2)
                    ];
                    $conflits[] = $conflit;
                }
            }
        }
        
        return $conflits;
    }

    private function getTypeConflit(Creneau $c1, Creneau $c2): string
    {
        if ($c1->getSalle() && $c2->getSalle() && $c1->getSalle()->getId() === $c2->getSalle()->getId()) {
            return 'salle';
        }
        if ($c1->getEnseignant() && $c2->getEnseignant() && $c1->getEnseignant()->getId() === $c2->getEnseignant()->getId()) {
            return 'enseignant';
        }
        if ($c1->getNiveau() && $c2->getNiveau() && $c1->getNiveau()->getId() === $c2->getNiveau()->getId()) {
            return 'niveau';
        }
        return 'horaire';
    }

    private function getCreneauStats(): array
    {
        $total = $this->entityManager->getRepository(Creneau::class)->count([]);
        $seances = $this->entityManager->getRepository(Creneau::class)->count(['typeCreneau' => TypeCreneau::SEANCE]);
        $pauses = $this->entityManager->getRepository(Creneau::class)->count(['typeCreneau' => TypeCreneau::PAUSE]);
        $heuresCreuses = $this->entityManager->getRepository(Creneau::class)->count(['typeCreneau' => TypeCreneau::HEURE_CREUSE]);

        return [
            'total' => $total,
            'seances' => $seances,
            'pauses' => $pauses,
            'heures_creuses' => $heuresCreuses
        ];
    }
} 