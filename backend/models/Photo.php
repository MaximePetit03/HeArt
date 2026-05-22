<?php

class Photo {
    public int $id;
    public string $title;
    public string $filename;
    public int $album_id;
    public int $user_id;
    public string $created_at;
    public bool $is_visible;
    public ?string $taken_at;

    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }


    public function getAlbumId(): int {
        return $this->album_id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function isVisible(): bool {
        return $this->is_visible;
    }

    public function getTakenAt(): ? string {
        return $this->taken_at;
    }

    public function getFilename(): string {
        return $this->filename;
    }

    public function getTakenAtFormatted(): string {
        if ($this->taken_at === null) {
            return 'Date non définie';
        }
        $date = new DateTime($this->taken_at);
        return $date->format('d/m/Y');
    }
}