<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'absence')]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'absences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Creneau::class, inversedBy: 'absences')]
    private ?Creneau $creneau = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateAbsence = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // 'etudiant' ou 'enseignant'

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    #[ORM\Column]
    private ?bool $justifiee = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $justification = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDeclaration = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $declarePar = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'en_attente'; // 'en_attente', 'approuvee', 'rejetee'

    public function __construct()
    {
        $this->dateDeclaration = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getCreneau(): ?Creneau
    {
        return $this->creneau;
    }

    public function setCreneau(?Creneau $creneau): self
    {
        $this->creneau = $creneau;
        return $this;
    }

    public function getDateAbsence(): ?\DateTimeInterface
    {
        return $this->dateAbsence;
    }

    public function setDateAbsence(\DateTimeInterface $dateAbsence): self
    {
        $this->dateAbsence = $dateAbsence;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function isJustifiee(): ?bool
    {
        return $this->justifiee;
    }

    public function setJustifiee(bool $justifiee): self
    {
        $this->justifiee = $justifiee;
        return $this;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(?string $justification): self
    {
        $this->justification = $justification;
        return $this;
    }

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(\DateTimeInterface $dateDeclaration): self
    {
        $this->dateDeclaration = $dateDeclaration;
        return $this;
    }

    public function getDeclarePar(): ?Utilisateur
    {
        return $this->declarePar;
    }

    public function setDeclarePar(?Utilisateur $declarePar): self
    {
        $this->declarePar = $declarePar;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
}