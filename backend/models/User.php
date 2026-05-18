<?php

class User extends AbstractModel {
    protected string $username = '';
    protected string $email = '';
    protected string $password = '';
    protected ?string $profilePhoto = null;

    public function __construct(
        string $username = '',
        string $email = '',
        string $password = '',
        ?string $profilePhoto = null
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->profilePhoto = $profilePhoto;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = strip_tags(trim($username));
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = strip_tags(trim($email));
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getProfilePhoto(): ?string {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): void {
        $this->profilePhoto = $profilePhoto ? strip_tags(trim($profilePhoto)) : null;
    }
}