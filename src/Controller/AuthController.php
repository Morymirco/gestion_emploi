<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UtilisateurRepository;
use App\Repository\CoursRepository;
use App\Repository\EmploiDuTempsRepository;

class AuthController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;
    private CoursRepository $coursRepository;
    private EmploiDuTempsRepository $emploiDuTempsRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository,
        CoursRepository $coursRepository,
        EmploiDuTempsRepository $emploiDuTempsRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->coursRepository = $coursRepository;
        $this->emploiDuTempsRepository = $emploiDuTempsRepository;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_dashboard');
            } elseif ($this->isGranted('ROLE_ENSEIGNANT')) {
                return $this->redirectToRoute('app_enseignant_dashboard');
            } elseif ($this->isGranted('ROLE_ETUDIANT')) {
                return $this->redirectToRoute('app_etudiant_dashboard');
            }
        }

        return $this->render('home/index.html.twig');
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userCount = $this->utilisateurRepository->count([]);
        $coursCount = $this->coursRepository->count([]);
        $scheduleCount = $this->emploiDuTempsRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userCount,
            'coursCount' => $coursCount,
            'scheduleCount' => $scheduleCount,
        ]);
    }

    #[Route('/enseignant/dashboard', name: 'app_enseignant_dashboard')]
    public function enseignantDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENSEIGNANT');
        return $this->render('enseignant/dashboard.html.twig');
    }

    #[Route('/etudiant/dashboard', name: 'app_etudiant_dashboard')]
    public function etudiantDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        return $this->render('etudiant/dashboard.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('motDePasse')->getData();
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setMotDePasse($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}