<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class EtudiantController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard', name: 'app_etudiant_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        /** @var Utilisateur $etudiant */
        $etudiant = $this->getUser();

        // Récupérer l'emploi du temps de l'étudiant selon son niveau
        $emploisDuTemps = [];
        $prochainsCours = [];
        
        if ($etudiant->getNiveau()) {
            $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
                ->createQueryBuilder('e')
                ->where('e.niveau = :niveau')
                ->andWhere('e.date >= :today')
                ->setParameter('niveau', $etudiant->getNiveau())
                ->setParameter('today', new \DateTime())
                ->orderBy('e.date', 'ASC')
                ->addOrderBy('e.heureDebut', 'ASC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            // Récupérer les prochains cours (cette semaine)
            $prochainsCours = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
                ->createQueryBuilder('e')
                ->where('e.niveau = :niveau')
                ->andWhere('e.date BETWEEN :startWeek AND :endWeek')
                ->setParameter('niveau', $etudiant->getNiveau())
                ->setParameter('startWeek', new \DateTime('monday this week'))
                ->setParameter('endWeek', new \DateTime('sunday this week'))
                ->orderBy('e.date', 'ASC')
                ->addOrderBy('e.heureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Récupérer les absences de l'étudiant
        $absences = $etudiant->getAbsences();

        // Statistiques
        $stats = [
            'cours_cette_semaine' => count($prochainsCours),
            'total_absences' => count($absences),
            'niveau' => $etudiant->getNiveau() ? $etudiant->getNiveau()->getNomNiveau() : 'Non défini'
        ];

        return $this->render('etudiant/dashboard.html.twig', [
            'etudiant' => $etudiant,
            'emploisDuTemps' => $emploisDuTemps,
            'prochainsCours' => $prochainsCours,
            'absences' => $absences,
            'stats' => $stats
        ]);
    }

    #[Route('/profil', name: 'etudiant_profil')]
    public function profil(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        /** @var Utilisateur $etudiant */
        $etudiant = $this->getUser();

        return $this->render('etudiant/profil.html.twig', [
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/emploi-du-temps', name: 'etudiant_emploi_du_temps')]
    public function emploiDuTemps(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        /** @var Utilisateur $etudiant */
        $etudiant = $this->getUser();

        $emploisDuTemps = [];
        
        if ($etudiant->getNiveau()) {
            $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
                ->createQueryBuilder('e')
                ->where('e.niveau = :niveau')
                ->setParameter('niveau', $etudiant->getNiveau())
                ->orderBy('e.date', 'ASC')
                ->addOrderBy('e.heureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('etudiant/emploi_du_temps.html.twig', [
            'emploisDuTemps' => $emploisDuTemps,
            'etudiant' => $etudiant
        ]);
    }

    #[Route('/mes-absences', name: 'etudiant_mes_absences')]
    public function mesAbsences(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        /** @var Utilisateur $etudiant */
        $etudiant = $this->getUser();

        $absences = $etudiant->getAbsences();

        return $this->render('etudiant/mes_absences.html.twig', [
            'absences' => $absences,
            'etudiant' => $etudiant
        ]);
    }
}