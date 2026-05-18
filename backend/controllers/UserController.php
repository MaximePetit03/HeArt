<?php

class UserController extends AbstractController {
    private UserManager $userManager;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        $this->userManager = new UserManager();
    }

    public function profile(?string $error = null, ?string $success = null): void {
        $user = $this->userManager->findById($_SESSION['user_id']);

        $this->render('user/profile', [
            'title'    => 'Mon Profil - HeArt',
            'extraCss' => 'profile',
            'user'     => $user,
            'error'    => $error,
            'success'  => $success
        ]);
    }

    public function updateProfile(): void {
        $userId = $_SESSION['user_id'];
        $user = $this->userManager->findById($userId);

        $currentPassword = trim($_POST['current_password'] ?? '');
        $securityError = $this->validateSecurity($currentPassword, $user->getPassword());
        if ($securityError !== null) {
            $this->profile($securityError);
            return;
        }

        $error = $this->handleAvatarUpload($user);
        
        if ($error === null) {
            $error = $this->handleTextInputs($user);
        }

        if ($error === null) {
            $this->userManager->update(
                $userId, 
                $user->getUsername(), 
                $user->getEmail(), 
                $user->getPassword(), 
                $user->getProfilePhoto()
            );
        }

        $this->profile($error, $error === null ? 'Paramètres mis à jour avec succès !' : null);
    }

    public function deleteAccount(): void {
        $userId = $_SESSION['user_id'];
        $this->userManager->delete($userId);

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->redirect('/');
    }

    private function validateSecurity(string $currentPassword, string $hashedPassword): ?string {
        if (empty($currentPassword)) {
            return 'Vous devez saisir votre mot de passe actuel pour valider les changements.';
        }
        if (!password_verify($currentPassword, $hashedPassword)) {
            return 'Le mot de passe actuel est incorrect.';
        }
        return null;
    }

    private function handleAvatarUpload(User $user): ?string {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return 'Format d\'image invalide (uniquement JPG, PNG, WEBP).';
        }
        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            return 'L\'image est trop lourde (maximum 2 Mo).';
        }

        $newFileName = 'avatar_' . uniqid() . '.' . $fileExtension;
        $uploadFileDir = BASE_PATH . '/public/assets/uploads/avatars/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFileDir . $newFileName)) {
            return 'Erreur technique lors de l\'enregistrement de la photo.';
        }

        if ($user->getProfilePhoto() && file_exists($uploadFileDir . $user->getProfilePhoto())) {
            unlink($uploadFileDir . $user->getProfilePhoto());
        }

        $user->setProfilePhoto($newFileName);
        return null;
    }

    private function handleTextInputs(User $user): ?string {
        $username    = trim($_POST['username'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');

        if (!empty($username) && $username !== $user->getUsername()) {
            if ($this->userManager->usernameExists($username)) {
                return 'Ce pseudo existe déjà.';
            }
            $user->setUsername($username);
            $_SESSION['username'] = $username;
        }

        if (!empty($email) && $email !== $user->getEmail()) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 'L\'adresse email saisie est invalide.';
            }
            if ($this->userManager->emailExists($email)) {
                return 'Cette adresse email est déjà utilisée.';
            }
            $user->setEmail($email);
        }

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                return 'Le nouveau mot de passe doit faire au moins 8 caractères.';
            }
            $options = ['memory_cost' => 64000, 'time_cost' => 4, 'threads' => 2];
            $user->setPassword(password_hash($newPassword, PASSWORD_ARGON2ID, $options));
        }

        return null;
    }
}