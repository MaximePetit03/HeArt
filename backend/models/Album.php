<?php

class Album extends AbstractModel {
    protected string $title = '';
    protected string $description = '';
    protected int $visibility = 1;
    protected ?int $userId = null;

    public function __construct(
        string $title = '',
        string $description = '',
        int $visibility = 1,
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
        // Nettoyage XSS : supprime les balises HTML/Script malveillantes
        $this->title = strip_tags(trim($title));
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = strip_tags(trim($description));
    }

    public function getVisibility(): int {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): void {
        $this->visibility = in_array($visibility, [0, 1]) ? $visibility : 0;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(?int $userId): void {
        $this->userId = $userId;
    }
}