<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/module')]
class AdminModuleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_module_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $modules = $this->entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->orderBy('m.anneeAcademique', 'DESC')
            ->addOrderBy('m.semestre', 'ASC')
            ->getQuery()
            ->getResult();

        $stats = $this->getModuleStats();

        return $this->render('admin/module/index.html.twig', [
            'modules' => $modules,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'admin_module_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $module = new Module();
        
        // Valeurs par défaut
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $module->setAnneeAcademique($currentYear . '-' . $nextYear);
        $module->setActif(true);

        $form = $this->createForm(ModuleFormType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($module);
            $this->entityManager->flush();

            $this->addFlash('success', 'Module créé avec succès.');

            return $this->redirectToRoute('admin_module_index');
        }

        return $this->render('admin/module/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_module_edit')]
    public function edit(Request $request, Module $module): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ModuleFormType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Module mis à jour avec succès.');

            return $this->redirectToRoute('admin_module_index');
        }

        return $this->render('admin/module/edit.html.twig', [
            'form' => $form->createView(),
            'module' => $module,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_module_delete', methods: ['POST'])]
    public function delete(Request $request, Module $module): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$module->getId(), $request->request->get('_token'))) {
            // Vérifier s'il y a des cours ou emplois du temps associés
            $coursCount = count($module->getCours());
            $emploisCount = count($module->getEmploisDuTemps());
            
            if ($coursCount > 0 || $emploisCount > 0) {
                $this->addFlash('error', 
                    'Impossible de supprimer ce module car il est utilisé par ' . 
                    ($coursCount + $emploisCount) . ' élément(s).');
            } else {
                $this->entityManager->remove($module);
                $this->entityManager->flush();

                $this->addFlash('success', 'Module supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('admin_module_index');
    }

    #[Route('/{id}/activate', name: 'admin_module_activate', methods: ['POST'])]
    public function activate(Module $module): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $module->setActif(!$module->isActif());
        $this->entityManager->flush();

        $status = $module->isActif() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Module {$status} avec succès.");

        return $this->redirectToRoute('admin_module_index');
    }

    private function getModuleStats(): array
    {
        $totalModules = $this->entityManager->getRepository(Module::class)->count([]);
        $modulesActifs = $this->entityManager->getRepository(Module::class)->count(['actif' => true]);
        
        $moduleAvecCours = $this->entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->select('m.nomModule, m.semestre, m.anneeAcademique, COUNT(c.id) as nb_cours, COUNT(e.id) as nb_emplois')
            ->leftJoin('m.cours', 'c')
            ->leftJoin('m.emploisDuTemps', 'e')
            ->groupBy('m.id')
            ->orderBy('nb_cours', 'DESC')
            ->getQuery()
            ->getResult();

        return [
            'total' => $totalModules,
            'actifs' => $modulesActifs,
            'inactifs' => $totalModules - $modulesActifs,
            'details' => $moduleAvecCours
        ];
    }
} 