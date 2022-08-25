<?php

namespace App\Entity;

use App\Repository\CoachRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=CoachRepository::class)
 */
class Coach extends Person
{

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Tipo de entrenador vacio")  
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "coach"})  
     */
    private $type_coach;
    
    /**
     * @var int
     * @ORM\Column(type="integer",nullable=true)     
     * @Serializer\Type("integer")
     * @Serializer\Groups({"all", "coach"})  
     */
    private $experience;
    

    public function getId(): ?int
    {
        return $this->id;
    }   

    public function getTypeCoach(): ?string
    {
        return $this->type_coach;
    }

    public function setTypeCoach(string $type_coach): self
    {
        $this->type_coach = $type_coach;

        return $this;
    }   
    
    public function getExperience(): int {
        return $this->experience;
    }
    
    public function setExperience(int $experience): void {
        $this->experience = $experience;
    }




   
}
