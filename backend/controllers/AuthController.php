<?php

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        require_once BASE_PATH . '/models/User.php';
        $this->userModel = new User();
    }

    // GET /login
    public function loginForm(): void
    {
        $this->render('auth/login', [
            'title'    => 'Connexion – HeArt',
            'extraCss' => 'auth',
        ]);
    }

    // POST /login
    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = null;

        if (empty($email) || empty($password)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Email ou mot de passe incorrect.';
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $this->redirect('/');
            }
        }

        $this->render('auth/login', [
            'title'    => 'Connexion – HeArt',
            'extraCss' => 'auth',
            'error'    => $error,
        ]);
    }

    // GET /register
    public function registerForm(): void
    {
        $this->render('auth/register', [
            'title'    => 'Inscription – HeArt',
            'extraCss' => 'auth',
        ]);
    }

    // POST /register
    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $error    = null;

        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif ($password !== $confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif ($this->userModel->emailExists($email)) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $this->userModel->create($username, $email, $password);
            $this->redirect('/login');
        }

        $this->render('auth/register', [
            'title'    => 'Inscription – HeArt',
            'extraCss' => 'auth',
            'error'    => $error,
        ]);
    }

    // GET /logout
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}