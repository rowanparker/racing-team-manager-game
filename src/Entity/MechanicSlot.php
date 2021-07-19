<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MechanicSlotRepository;
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
 * @ORM\Entity(repositoryClass=MechanicSlotRepository::class)
 */
class MechanicSlot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="mechanicSlots")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Team $team;

    /**
     * @ORM\OneToOne(targetEntity=HiredMechanic::class, mappedBy="mechanicSlot", cascade={"persist", "remove"})
     */
    private ?HiredMechanic $hiredMechanic = null;

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

    public function getHiredMechanic(): ?HiredMechanic
    {
        return $this->hiredMechanic;
    }

    public function setHiredMechanic(HiredMechanic $hiredMechanic): self
    {
        // set the owning side of the relation if necessary
        if ($hiredMechanic->getMechanicSlot() !== $this) {
            $hiredMechanic->setMechanicSlot($this);
        }

        $this->hiredMechanic = $hiredMechanic;

        return $this;
    }
}
