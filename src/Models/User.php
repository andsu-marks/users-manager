<?php
namespace Src\Models;

class User implements \JsonSerializable {
    private ?int $id;
    private string $name;
    private string $email;
    private string $password;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        string $email,
        string $password,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setName(string $name): void { $this->name = $name; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}