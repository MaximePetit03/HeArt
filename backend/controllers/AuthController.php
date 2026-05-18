<?php

class AuthController extends AbstractController {
    private UserManager $userManager;

    public function __construct() {
        $this->userManager = new UserManager();
    }

    public function loginForm(): void {
        $this->render('auth/login', [
            'title'    => 'Connexion - HeArt',
            'extraCss' => 'auth',
        ]);
    }

    public function login(): void {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = null;

        if (empty($username) || empty($password)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            $user = $this->userManager->findByUsername($username);

            if (!$user || !password_verify($password, $user->getPassword())) {
                $error = 'Pseudo ou mot de passe incorrect.';
            } else {
                $_SESSION['user_id']   = $user->getId();
                $_SESSION['username']  = $user->getUsername();
                $this->redirect('/');
                return;
            }
        }

        $this->render('auth/login', [
            'title'    => 'Connexion – HeArt',
            'extraCss' => 'auth',
            'error'    => $error,
        ]);
    }

    public function registerForm(): void {
        $this->render('auth/register', [
            'title'    => 'Inscription – HeArt',
            'extraCss' => 'auth',
        ]);
    }

    public function register(): void {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $error    = null;

        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $error = 'Tous les champs sont obligatoires.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } else if (strlen($password) < 8) {
            $error = 'Le mot de passe doit faire au moins 8 caractères.';
        } else if ($password !== $confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else if ($this->userManager->usernameExists($username)) {
            $error = 'Ce pseudo est déjà utilisé.';
        } else if ($this->userManager->emailExists($email)) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $options = [
                'memory_cost' => 64000,
                'time_cost'   => 4,
                'threads'     => 2,
            ];

            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, $options);

            $this->userManager->create($username, $email, $hashedPassword);
            $this->redirect('/login');
            return;
        }

        $this->render('auth/register', [
            'title'    => 'Inscription - HeArt',
            'extraCss' => 'auth',
            'error'    => $error,
        ]);
    }

    public function logout(): void {
        session_destroy();
        $this->redirect('/login');
    }
}