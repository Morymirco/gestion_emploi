<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Enum\SalleType;
use App\Form\SalleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/salle')]
class AdminSalleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_salle_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Filtres
        $typeFilter = $request->query->get('type');
        $capaciteMin = $request->query->get('capacite_min');
        $capaciteMax = $request->query->get('capacite_max');
        $recherche = $request->query->get('recherche');

        $queryBuilder = $this->entityManager->getRepository(Salle::class)
            ->createQueryBuilder('s')
            ->orderBy('s.nomSalle', 'ASC');

        if ($typeFilter) {
            $queryBuilder->andWhere('s.type = :type')
                        ->setParameter('type', SalleType::from($typeFilter));
        }

        if ($capaciteMin) {
            $queryBuilder->andWhere('s.capacite >= :capaciteMin')
                        ->setParameter('capaciteMin', $capaciteMin);
        }

        if ($capaciteMax) {
            $queryBuilder->andWhere('s.capacite <= :capaciteMax')
                        ->setParameter('capaciteMax', $capaciteMax);
        }

        if ($recherche) {
            $queryBuilder->andWhere('s.nomSalle LIKE :search')
                        ->setParameter('search', '%' . $recherche . '%');
        }

        $salles = $queryBuilder->getQuery()->getResult();

        // Statistiques
        $totalSalles = $this->entityManager->getRepository(Salle::class)->count([]);
        $occupationStats = $this->getOccupationStats();

        return $this->render('admin/salle/index.html.twig', [
            'salles' => $salles,
            'totalSalles' => $totalSalles,
            'occupationStats' => $occupationStats,
            'filtres' => [
                'type' => $typeFilter,
                'capacite_min' => $capaciteMin,
                'capacite_max' => $capaciteMax,
                'recherche' => $recherche,
            ]
        ]);
    }

    #[Route('/new', name: 'admin_salle_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $salle = new Salle();
        $form = $this->createForm(SalleFormType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($salle);
            $this->entityManager->flush();

            $this->addFlash('success', 'Salle créée avec succès.');

            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('admin/salle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_salle_edit')]
    public function edit(Request $request, Salle $salle): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(SalleFormType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Salle mise à jour avec succès.');

            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('admin/salle/edit.html.twig', [
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_salle_delete', methods: ['POST'])]
    public function delete(Request $request, Salle $salle): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$salle->getId(), $request->request->get('_token'))) {
            // Vérifier s'il y a des emplois du temps associés
            $emploisCount = count($salle->getEmploiDuTemps());
            
            if ($emploisCount > 0) {
                $this->addFlash('error', 
                    'Impossible de supprimer cette salle car elle est utilisée dans ' . $emploisCount . ' emploi(s) du temps.');
            } else {
                $this->entityManager->remove($salle);
                $this->entityManager->flush();

                $this->addFlash('success', 'Salle supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('admin_salle_index');
    }

    #[Route('/{id}/disponibilite', name: 'admin_salle_disponibilite')]
    public function disponibilite(Salle $salle): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer les emplois du temps pour cette salle
        $emplois = $this->entityManager->getRepository(\App\Entity\EmploiDuTemps::class)
            ->createQueryBuilder('e')
            ->where('e.salle = :salle')
            ->andWhere('e.date >= :dateDebut')
            ->andWhere('e.date <= :dateFin')
            ->setParameter('salle', $salle)
            ->setParameter('dateDebut', new \DateTime())
            ->setParameter('dateFin', (new \DateTime())->modify('+30 days'))
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/salle/disponibilite.html.twig', [
            'salle' => $salle,
            'emplois' => $emplois,
        ]);
    }

    private function getOccupationStats(): array
    {
        $sallesAvecEmplois = $this->entityManager->getRepository(Salle::class)
            ->createQueryBuilder('s')
            ->select('s.nomSalle, s.capacite, COUNT(e.id) as nb_utilisations')
            ->leftJoin('s.emploiDuTemps', 'e')
            ->groupBy('s.id')
            ->orderBy('nb_utilisations', 'DESC')
            ->getQuery()
            ->getResult();

        return $sallesAvecEmplois;
    }
} 