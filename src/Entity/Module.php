<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'module')]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nomModule = null;

    #[ORM\Column(length: 20)]
    private ?string $code = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 10)]
    private ?string $anneeAcademique = null;

    #[ORM\Column]
    private ?int $semestre = null; // 1 ou 2

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\OneToMany(targetEntity: EmploiDuTemps::class, mappedBy: 'module')]
    private Collection $emploisDuTemps;

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'module')]
    private Collection $cours;

    public function __construct()
    {
        $this->emploisDuTemps = new ArrayCollection();
        $this->cours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomModule(): ?string
    {
        return $this->nomModule;
    }

    public function setNomModule(string $nomModule): self
    {
        $this->nomModule = $nomModule;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getAnneeAcademique(): ?string
    {
        return $this->anneeAcademique;
    }

    public function setAnneeAcademique(string $anneeAcademique): self
    {
        $this->anneeAcademique = $anneeAcademique;
        return $this;
    }

    public function getSemestre(): ?int
    {
        return $this->semestre;
    }

    public function setSemestre(int $semestre): self
    {
        $this->semestre = $semestre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getEmploisDuTemps(): Collection
    {
        return $this->emploisDuTemps;
    }

    public function addEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if (!$this->emploisDuTemps->contains($emploiDuTemps)) {
            $this->emploisDuTemps->add($emploiDuTemps);
            $emploiDuTemps->setModule($this);
        }

        return $this;
    }

    public function removeEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if ($this->emploisDuTemps->removeElement($emploiDuTemps)) {
            if ($emploiDuTemps->getModule() === $this) {
                $emploiDuTemps->setModule(null);
            }
        }

        return $this;
    }

    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCours(Cours $cours): self
    {
        if (!$this->cours->contains($cours)) {
            $this->cours->add($cours);
            $cours->setModule($this);
        }

        return $this;
    }

    public function removeCours(Cours $cours): self
    {
        if ($this->cours->removeElement($cours)) {
            if ($cours->getModule() === $this) {
                $cours->setModule(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nomModule . ' (S' . $this->semestre . ' - ' . $this->anneeAcademique . ')';
    }
} 