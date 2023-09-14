<?php

namespace App\Entity;

use App\Repository\CommandesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandesRepository::class)]
class Commandes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $numero_commande = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_commande = null;

    #[ORM\OneToMany(mappedBy: 'commandes', targetEntity: Utilisateur::class)]
    private Collection $utilisateur;

    public function __construct()
    {
        $this->utilisateur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numero_commande;
    }

    public function setNumeroCommande(?string $numero_commande): static
    {
        $this->numero_commande = $numero_commande;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->date_commande;
    }

    public function setDateCommande(?\DateTimeInterface $date_commande): static
    {
        $this->date_commande = $date_commande;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateur(): Collection
    {
        return $this->utilisateur;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateur->contains($utilisateur)) {
            $this->utilisateur->add($utilisateur);
            $utilisateur->setCommandes($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateur->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getCommandes() === $this) {
                $utilisateur->setCommandes(null);
            }
        }

        return $this;
    }
}
