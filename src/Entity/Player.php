<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player extends Person
{

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Posicion del jugador vacia")   
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "player"})  
     */
    private $positions;

    /**
     * @var float
     * @ORM\Column(type="float",nullable=true)
     * @Assert\Positive(message="")
     * @Serializer\Type("float")
     * @Serializer\Groups({"all", "player"})
     */
    private $height;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Positive(message="")
     * @Serializer\Type("float")
     * @Serializer\Groups({"all", "player"})
     */
    private $weight;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="")
     * @Assert\NotBlank(message="Numero de camiseta vacia") 
     * @Serializer\Type("int")
     * @Serializer\Groups({"all", "player"})   
     */
    private $number_shirt; 
    

    public function getId(): ?int
    {
        return $this->id;
    } 

    public function getPositions(): ?string
    {
        return $this->positions;
    }

    public function setPositions(string $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getNumberShirt(): ?int
    {
        return $this->number_shirt;
    }

    public function setNumberShirt(int $number_shirt): self
    {
        $this->number_shirt = $number_shirt;

        return $this;
    }
    
    public function getPerson(): ?Person {
        return $this->person;
    }
    
    public function setPerson(Person $person): void {
        $this->person = $person;
    }   
}
