<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_ADMIN")'
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['user.collection.post']],
            'validation_groups' => ['user.collection.post']
        ]
    ],
    itemOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_ADMIN") or object == user',
        ],
        'patch' => [
            'security' => 'is_granted("ROLE_ADMIN")',
            'denormalization_context' => ['groups' => ['user.item.patch']],
        ],
        'delete' => [
            'security' => 'is_granted("ROLE_ADMIN")',
        ]
    ],
    normalizationContext: ['groups' => ['user.item.get']],
)]
#[UniqueEntity(
    fields: ['username'],
    groups: ['user.collection.post']
)]
#[UniqueEntity(
    fields: ['team'],
    ignoreNull: true,
)]
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    #[Groups([
        'user.collection.get',
        'user.item.get',
    ])]
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    #[Groups([
        'user.collection.get',
        'user.collection.post',
        'user.item.get',
        'user.item.patch',
    ])]
    #[Assert\NotBlank(
        message: "User must have a username",
        allowNull: false,
        groups: [
            'user.collection.post',
            'user.item.patch',
        ],
    )]
    #[Assert\Regex(
        pattern: "/^[a-z0-9]*$/",
        message: 'Must be alpha-numeric and lower case.',
        groups: [
            'user.collection.post',
            'user.item.patch',
        ]
    )]
    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    #[Groups([
        'user.collection.post',
    ])]
    private ?string $plainPassword = null;

    #[Assert\NotBlank(
        message: "User must have a password",
        allowNull: false,
        groups: [
            'user.collection.post',
        ],
    )]
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $password = null;

    /**
     * @ORM\OneToOne(targetEntity=Team::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private $team;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = strtolower(trim($username));

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @Ignore
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
