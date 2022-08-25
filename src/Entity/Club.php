<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;


/**
 * @ORM\Entity(repositoryClass=ClubRepository::class)
 */
class Club
{
    /**
     * @var int     
     * @ORM\Id     
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Type("int")
     * @Serializer\Groups({"all", "club"})
     */
    private $id;

    /**
     * @var string     
     * @ORM\Column(type="string", length=100)
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "club"})
     * @Assert\NotBlank(message="Nombre vacio")  
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "club"})
     */
    private $name_manager;

    /**
     * @var float
     * @ORM\Column(type="float")
     * @Serializer\Type("float")
     * @Assert\NotBlank(message="Presupesto vacio")  
     * @Assert\Positive(message="Presupuesto mayor que cero")
     * @Serializer\Groups({"all", "club"})
     */
    private $budget;   
    
    /**
     * @var Person      
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="club")
     * @Serializer\Type("ArrayCollection<App\Entity\Person>")
     * @Serializer\Groups({"all", "club"})
     */
    private $person;
    

    public function getId(): ?int
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

    public function getNameManager(): ?string
    {
        return $this->name_manager;
    }

    public function setNameManager(?string $name_manager): self
    {
        $this->name_manager = $name_manager;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(float $budget): self
    {
        $this->budget = $budget;

        return $this;
    }
    
    public function getPersons(): ?Person {
        return $this->persons;
    }
    
    public function setPersons(Person $persons): void {
        $this->persons = $persons;
    }
}
