<?php
namespace App\Model\Class;

use DateTime;

class User {
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $mail;
    private string $password;
    private string $pseudo;
    private array $roles;
    private DateTime $registeredAt;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $mail,
        string $password,
        string $pseudo,
        array $roles = ['ROLE_USER'],
        DateTime $registeredAt
    ) {
        $this->setId($id);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setMail($mail);
        $this->setPassword($password);
        $this->setPseudo($pseudo);
        $this->setRoles($roles);
        $this->setRegisteredAt($registeredAt);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array {
        return array(
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'mail' => $this->getMail(),
            'password' => $this->getPassword(),
            'pseudo' => $this->getPseudo(),
            'roles' => $this->getRoles(),
            'registeredAt' => $this->getRegisteredAt()
        );
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function setMail(string $mail): void {
        $this->mail = $mail;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void {
        $this->pseudo = $pseudo;
    }

    public function getRegisteredAt(): DateTime {
        return $this->registeredAt;
    }

    public function setRegisteredAt(DateTime $registeredAt): void {
        $this->registeredAt = $registeredAt;
    }

    public function getRoles(): array {
        return $this->roles;
    }

    public function setRoles(array $roles): void {
        $this->roles = $roles;
    }
}