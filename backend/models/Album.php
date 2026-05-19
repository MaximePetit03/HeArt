<?php

class Album extends AbstractModel {
    protected string $title = '';
    protected string $description = '';
    protected string $visibility = 'public';
    protected ?int $userId = null;
    protected ?string $username = null;

    public function __construct(
        string $title = '',
        string $description = '',
        string $visibility = 'public',
        ?int $userId = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->visibility = $visibility;
        $this->userId = $userId;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = strip_tags(trim($title));
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = strip_tags(trim($description));
    }

    public function getVisibility(): string {
        return $this->visibility;
    }

    public function setVisibility($visibility): void {
        $allowed = ['public', 'private', 'restricted'];
        $this->visibility = in_array($visibility, $allowed) ? $visibility : 'public';
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(?int $userId): void {
        $this->userId = $userId;
    }

    public function getUsername(): string {
        return $this->username ?? 'Utilisateur inconnu';
    }

    public function setUsername(?string $username): void {
        $this->username = $username;
    }

    public function getFormattedDate(): string {
        $date = $this->getCreatedAt();
        
        if (!$date) {
            return date('d/m/Y');
        }
        
        return $date->format('d/m/Y');
    }
}