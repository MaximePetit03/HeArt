<?php

class Comment {
    private int $id;
    private string $content;
    private int $user_id;
    private int $photo_id;
    private string $created_at;
    private ?string $updated_at = null;
    private ?string $username = null;

    public function getId(): int {
        return $this->id;
    }
    public function getContent(): string {
        return $this->content;
    }
    public function getUserId(): int {
        return $this->user_id;
    }
    public function getPhotoId(): int {
        return $this->photo_id;
    }
    public function getCreatedAt(): string {
        $date = new DateTime($this->created_at);
        $date->setTimezone(new DateTimeZone('Europe/Paris'));
        
        return $date->format('d/m/Y');
    }
    
    public function getUsername(): string {
        return $this->username ?? 'Utilisateur inconnu';
    }
    
    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }
}