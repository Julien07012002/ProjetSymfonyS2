<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PaiementCarteBancaire extends Paiement
{
    /**
     * @ORM\Column(type="string", length=16)
     */
    private $numeroCarte;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $codeSecurite;

    public function getNumeroCarte(): ?string
    {
        return $this->numeroCarte;
    }

    public function setNumeroCarte(string $numeroCarte): self
    {
        $this->numeroCarte = $numeroCarte;

        return $this;
    }

    public function getCodeSecurite(): ?string
    {
        return $this->codeSecurite;
    }

    public function setCodeSecurite(string $codeSecurite): self
    {
        $this->codeSecurite = $codeSecurite;

        return $this;
    }
}