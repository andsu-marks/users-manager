<?php
namespace Src\Models;

/**
 * Represents a User entity in the system.
 * 
 * This model stores user data, including name, email, hashed password, and timestamps. It also implements
 * JsonSerialize to control how the object is exposed in API responses.
 */
class User implements \JsonSerializable {
    /**
     * @var int|null User ID (null whtn not persisted yet)
     */
    private ?int $id;

    /**
     * @var string User name
     */
    private string $name;

    /**
     * @var string User email.
     */
    private string $email;

    /**
     * @var string Hashed User password.
     */
    private string $password;

    /**
     * @var string|null Timestamp of when the user was created.
     */
    private ?string $createdAt;

    /**
     * @var string|null Timestamp of last user update
     */
    private ?string $updatedAt;

    /**
     * User constructor
     * 
     * @param int|null $id User ID (null of neq users)
     * @param string $name User name
     * @param string $email User email
     * @param string $password Hashed user password
     * @param string|null $createdAt Created timestamp
     * @param string|null $updatedAt updated timestamp
     */
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

    /** @return int|null */
    public function getId(): ?int { return $this->id; }

    /** @return string */
    public function getName(): string { return $this->name; }

    /** @return string */
    public function getEmail(): string { return $this->email; }

    /** @return string */
    public function getPassword(): string { return $this->password; }

    /** @return string|null */
    public function getCreatedAt(): ?string { return $this->createdAt; }

    /** @return string|null */
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    /** @param string $name */
    public function setName(string $name): void { $this->name = $name; }

    /** @param string $password */
    public function setPassword(string $password): void { $this->password = $password; }

    /** @param string $email */
    public function setEmail(string $email): void { $this->email = $email; }

    /**
     * Converts the model into an array for JSON responses.
     * 
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}