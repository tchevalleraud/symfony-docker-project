<?php
    namespace App\Domain\_mysql\System\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Ramsey\Uuid\Doctrine\UuidGenerator;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Entity(repositoryClass="App\Domain\_mysql\System\Repository\UserRepository")
     * @ORM\Table(name="system_user")
     * @UniqueEntity("email")
     */
    class User implements UserInterface, PasswordAuthenticatedUserInterface {

        /**
         * @ORM\Id()
         * @ORM\GeneratedValue(strategy="CUSTOM")
         * @ORM\CustomIdGenerator(class=UuidGenerator::class)
         * @ORM\Column(type="string", unique=true)
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=180, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(type="string")
         */
        private $password;

        /**
         * @ORM\Column(type="string")
         */
        private $apiToken;

        /**
         * @ORM\Column(type="json")
         */
        private $roles = [];

        public function __construct() {
        }

        public function getId(): ?string {
            return $this->id;
        }

        public function setId($id): self {
            $this->id = $id;
            return $this;
        }

        public function getEmail(): ?string {
            return $this->email;
        }

        public function setEmail($email): self {
            $this->email = $email;
            return $this;
        }

        public function getUserIdentifier(): string {
            return $this->getEmail();
        }

        public function getPassword(): ?string {
            return $this->password;
        }

        public function setPassword($password): self {
            $this->password = $password;
            return $this;
        }

        public function getSalt(): ?string {
            return null;
        }

        public function getApiToken(): ?string {
            return $this->apiToken;
        }

        public function setApiToken($apiToken): self {
            $this->apiToken = $apiToken;
            return $this;
        }

        public function getRoles(): array {
            $roles = $this->roles;
            $roles[] = 'ROLE_USER';

            return array_unique($roles);
        }

        public function setRoles(array $roles): self {
            $this->roles = $roles;
            return $this;
        }

        public function eraseCredentials() {
            // TODO: Implement eraseCredentials() method.
        }

    }