<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\GarageSlotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")'
        ],
        'post' => [
            'security' => 'is_granted("ROLE_USER")'
        ]
    ],
    itemOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")',
        ],
        'delete' => [
            'security' => 'is_granted("ROLE_ADMIN")',
        ]
    ],
)]
/**
 * @ORM\Entity(repositoryClass=GarageSlotRepository::class)
 */
class GarageSlot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="garageSlots")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Team $team;

    /**
     * @ORM\OneToOne(targetEntity=OwnedCar::class, mappedBy="garageSlot", cascade={"persist", "remove"})
     */
    private ?OwnedCar $ownedCar = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOwnedCar(): ?OwnedCar
    {
        return $this->ownedCar;
    }

    public function setOwnedCar(OwnedCar $ownedCar): self
    {
        // set the owning side of the relation if necessary
        if ($ownedCar->getGarageSlot() !== $this) {
            $ownedCar->setGarageSlot($this);
        }

        $this->ownedCar = $ownedCar;

        return $this;
    }
}
