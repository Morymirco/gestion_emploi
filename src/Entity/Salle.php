<?php

namespace App\Entity;

use App\Enum\SalleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomSalle = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(type: 'string', enumType: SalleType::class)]
    private ?SalleType $type = null;

    #[ORM\OneToMany(targetEntity: EmploiDuTemps::class, mappedBy: 'salle')]
    private Collection $emploiDuTemps;

    #[ORM\OneToMany(targetEntity: Disponibilité::class, mappedBy: 'salle')]
    private Collection $disponibilites;

    public function __construct()
    {
        $this->emploiDuTemps = new ArrayCollection();
        $this->disponibilites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalle(): ?string
    {
        return $this->nomSalle;
    }

    public function setNomSalle(string $nomSalle): self
    {
        $this->nomSalle = $nomSalle;
        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getType(): ?SalleType
    {
        return $this->type;
    }

    public function setType(SalleType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getEmploiDuTemps(): Collection
    {
        return $this->emploiDuTemps;
    }

    public function addEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if (!$this->emploiDuTemps->contains($emploiDuTemps)) {
            $this->emploiDuTemps->add($emploiDuTemps);
            $emploiDuTemps->setSalle($this);
        }
        return $this;
    }

    public function removeEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemps)) {
            if ($emploiDuTemps->getSalle() === $this) {
                $emploiDuTemps->setSalle(null);
            }
        }
        return $this;
    }

    public function getDisponibilites(): Collection
    {
        return $this->disponibilites;
    }

    public function addDisponibilite(Disponibilité $disponibilite): self
    {
        if (!$this->disponibilites->contains($disponibilite)) {
            $this->disponibilites->add($disponibilite);
            $disponibilite->setSalle($this);
        }
        return $this;
    }

    public function removeDisponibilite(Disponibilité $disponibilite): self
    {
        if ($this->disponibilites->removeElement($disponibilite)) {
            if ($disponibilite->getSalle() === $this) {
                $disponibilite->setSalle(null);
            }
        }
        return $this;
    }
}