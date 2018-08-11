<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * 
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_product_show",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 * 
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_product_show",
 *          
 *      )
 * )
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $screnSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $processor;

    /**
     * @ORM\Column(type="integer")
     */
    private $ram;

    /**
     * @ORM\Column(type="integer")
     */
    private $memory;

    /**
     * @ORM\Column(type="integer")
     */
    private $photoDef;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $os;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bande;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $battery;
    

    public function getId()
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

    public function getScrenSize(): ?int
    {
        return $this->screnSize;
    }

    public function setScrenSize(?int $screnSize): self
    {
        $this->screnSize = $screnSize;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(?string $processor): self
    {
        $this->processor = $processor;

        return $this;
    }

    public function getRam(): ?int
    {
        return $this->ram;
    }

    public function setRam(int $ram): self
    {
        $this->ram = $ram;

        return $this;
    }

    public function getMemory(): ?int
    {
        return $this->memory;
    }

    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getPhotoDef(): ?int
    {
        return $this->photoDef;
    }

    public function setPhotoDef(int $photoDef): self
    {
        $this->photoDef = $photoDef;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getBande(): ?int
    {
        return $this->bande;
    }

    public function setBande(int $bande): self
    {
        $this->bande = $bande;

        return $this;
    }
    
    public function getBattery(): ?int
    {
        return $this->battery;
    }

    public function setBaterry(int $battery): self
    {
        $this->battery = $battery;

        return $this;
    }

    public function setBattery(?int $battery): self
    {
        $this->battery = $battery;

        return $this;
    }
}
