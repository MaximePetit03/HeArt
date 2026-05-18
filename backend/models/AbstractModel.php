<?php

abstract class AbstractModel {
    protected ?int $id = null;
    protected ?DateTime $createdAt = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        if ($this->id !== null && $id !== null) {
            throw new IllegalStateException();
        }
        $this->id = $id;
    }

    public function getCreatedAt(): ?DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime|string|null $createdAt): void {
        if (is_string($createdAt)) {
            $this->createdAt = new DateTime($createdAt);
        } else {
            $this->createdAt = $createdAt;
        }
    }
}