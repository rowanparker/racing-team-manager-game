<?php

namespace App\Entity;

use App\Repository\HiredDriverRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HiredDriverRepository::class)
 */
class HiredDriver
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity=DriverSlot::class, inversedBy="hiredDriver", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private DriverSlot $driverSlot;

    /**
     * @ORM\ManyToOne(targetEntity=Driver::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Driver $driver;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriverSlot(): ?DriverSlot
    {
        return $this->driverSlot;
    }

    public function setDriverSlot(DriverSlot $driverSlot): self
    {
        $this->driverSlot = $driverSlot;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }
}
