<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enseignant')]
class EnseignantController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard', name: 'app_enseignant_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENSEIGNANT');
        
        /** @var Utilisateur $enseignant */
        $enseignant = $this->getUser();

        // Récupérer les cours de l'enseignant
        $cours = $enseignant->getCours();

        // Récupérer les emplois du temps de l'enseignant
        $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cours', 'c')
            ->where('c.enseignant = :enseignant')
            ->andWhere('e.date >= :today')
            ->setParameter('enseignant', $enseignant)
            ->setParameter('today', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Récupérer les prochains cours (cette semaine)
        $prochainsCours = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cours', 'c')
            ->where('c.enseignant = :enseignant')
            ->andWhere('e.date BETWEEN :startWeek AND :endWeek')
            ->setParameter('enseignant', $enseignant)
            ->setParameter('startWeek', new \DateTime('monday this week'))
            ->setParameter('endWeek', new \DateTime('sunday this week'))
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        // Statistiques
        $stats = [
            'total_cours' => count($cours),
            'cours_cette_semaine' => count($prochainsCours),
            'total_emplois' => count($emploisDuTemps)
        ];

        return $this->render('enseignant/dashboard.html.twig', [
            'enseignant' => $enseignant,
            'cours' => $cours,
            'emploisDuTemps' => $emploisDuTemps,
            'prochainsCours' => $prochainsCours,
            'stats' => $stats
        ]);
    }

    #[Route('/profil', name: 'enseignant_profil')]
    public function profil(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENSEIGNANT');
        
        /** @var Utilisateur $enseignant */
        $enseignant = $this->getUser();

        return $this->render('enseignant/profil.html.twig', [
            'enseignant' => $enseignant,
        ]);
    }

    #[Route('/mes-cours', name: 'enseignant_mes_cours')]
    public function mesCours(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENSEIGNANT');
        
        /** @var Utilisateur $enseignant */
        $enseignant = $this->getUser();

        $cours = $enseignant->getCours();

        return $this->render('enseignant/mes_cours.html.twig', [
            'cours' => $cours,
            'enseignant' => $enseignant
        ]);
    }

    #[Route('/emploi-du-temps', name: 'enseignant_emploi_du_temps')]
    public function emploiDuTemps(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENSEIGNANT');
        
        /** @var Utilisateur $enseignant */
        $enseignant = $this->getUser();

        // Récupérer l'emploi du temps de l'enseignant
        $emploisDuTemps = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cours', 'c')
            ->where('c.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant)
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('enseignant/emploi_du_temps.html.twig', [
            'emploisDuTemps' => $emploisDuTemps,
            'enseignant' => $enseignant
        ]);
    }
}