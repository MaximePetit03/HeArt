<?php

class UserManager extends AbstractManager {
    protected string $table = 'user';
    protected string $entityClass = 'User';

    public function findById(int $id): object|false {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT * FROM `{$table}` WHERE id = :id");
        $query->execute([':id' => $id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $this->hydrate($data);
    }

    public function findByUsername(string $username): object|false {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT * FROM `{$table}` WHERE username = :username");
        $query->execute([':username' => $username]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $this->hydrate($data);
    }

    public function findByEmail(string $email): object|false {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT * FROM `{$table}` WHERE email = :email");
        $query->execute([':email' => $email]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $this->hydrate($data);
    }

    public function create(string $username, string $email, string $hashedPassword): void {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("
            INSERT INTO `{$table}` (username, email, password)
            VALUES (:username, :email, :password)
        ");
        
        $query->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hashedPassword,
        ]);
    }

    public function usernameExists(string $username): bool {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT COUNT(*) FROM `{$table}` WHERE username = :username");
        $query->execute([':username' => $username]);
        
        return $query->fetchColumn() > 0;
    }

    public function emailExists(string $email): bool {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT COUNT(*) FROM `{$table}` WHERE email = :email");
        $query->execute([':email' => $email]);
        
        return $query->fetchColumn() > 0;
    }

    public function update(int $id, string $username, string $email, string $hashedPassword, ?string $profilePhoto): void {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("
            UPDATE `{$table}` 
            SET username = :username, email = :email, password = :password, profile_photo = :profile_photo
            WHERE id = :id
        ");
        $query->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password'      => $hashedPassword,
            ':profile_photo' => $profilePhoto,
            ':id'            => $id
        ]);
    }

    public function delete(int $id): void {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("DELETE FROM `{$table}` WHERE id = :id");
        $query->execute([':id' => $id]);
    }

    public function findAll(): array {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT id, username FROM `{$table}`");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllOther(int $excludedUserId): array {
        $table = $this->getSecureTable();
        $query = $this->db->prepare("SELECT id, username FROM `{$table}` WHERE id != :id");
        $query->execute([':id' => $excludedUserId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}