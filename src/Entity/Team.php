<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")',
        ],
        'post' => [
            'security' => 'is_granted("ROLE_USER")',
        ]
    ],
    itemOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")',
        ],
        'patch' => [
            'security' => 'is_granted("TEAM_EDIT", object)',
        ],
        'delete' => [
            'security' => 'is_granted("ROLE_ADMIN")',
        ]
    ],
)]
/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity=GarageSlot::class, mappedBy="team", orphanRemoval=true)
     */
    private Collection $garageSlots;

    /**
     * @ORM\OneToMany(targetEntity=DriverSlot::class, mappedBy="team", orphanRemoval=true, fetch="EAGER")
     */
    private Collection $driverSlots;

    /**
     * @ORM\OneToMany(targetEntity=MechanicSlot::class, mappedBy="team", orphanRemoval=true)
     */
    private Collection $mechanicSlots;

    /**
     * @ORM\Column(type="integer")
     */
    private int $balanceCredits;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="team", cascade={"persist", "remove"})
     */
    private ?User $user;

    public function __construct()
    {
        $this->garageSlots = new ArrayCollection();
        $this->driverSlots = new ArrayCollection();
        $this->mechanicSlots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array|GarageSlot[]
     */
    public function getGarageSlots(): array
    {
        return $this->garageSlots->getValues();
    }

    public function addGarageSlot(GarageSlot $garageSlot): self
    {
        if (!$this->garageSlots->contains($garageSlot)) {
            $this->garageSlots[] = $garageSlot;
            $garageSlot->setTeam($this);
        }

        return $this;
    }

    public function removeGarageSlot(GarageSlot $garageSlot): self
    {
        if ($this->garageSlots->removeElement($garageSlot)) {
            // set the owning side to null (unless already changed)
            if ($garageSlot->getTeam() === $this) {
                $garageSlot->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return array|DriverSlot[]
     */
    public function getDriverSlots(): array
    {
        return $this->driverSlots->getValues();
    }

    public function addDriverSlot(DriverSlot $driverSlot): self
    {
        if (!$this->driverSlots->contains($driverSlot)) {
            $this->driverSlots[] = $driverSlot;
            $driverSlot->setTeam($this);
        }

        return $this;
    }

    public function removeDriverSlot(DriverSlot $driverSlot): self
    {
        if ($this->driverSlots->removeElement($driverSlot)) {
            // set the owning side to null (unless already changed)
            if ($driverSlot->getTeam() === $this) {
                $driverSlot->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return array|MechanicSlot[]
     */
    public function getMechanicSlots(): array
    {
        return $this->mechanicSlots->getValues();
    }

    public function addMechanicSlot(MechanicSlot $mechanicSlot): self
    {
        if (!$this->mechanicSlots->contains($mechanicSlot)) {
            $this->mechanicSlots[] = $mechanicSlot;
            $mechanicSlot->setTeam($this);
        }

        return $this;
    }

    public function removeMechanicSlot(MechanicSlot $mechanicSlot): self
    {
        if ($this->mechanicSlots->removeElement($mechanicSlot)) {
            // set the owning side to null (unless already changed)
            if ($mechanicSlot->getTeam() === $this) {
                $mechanicSlot->setTeam(null);
            }
        }

        return $this;
    }

    public function getBalanceCredits(): ?int
    {
        return $this->balanceCredits;
    }

    public function setBalanceCredits(int $balanceCredits): self
    {
        $this->balanceCredits = $balanceCredits;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $user->setTeam($this);
        $this->user = $user;

        return $this;
    }
}
