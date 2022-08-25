<?php

namespace App\Entity;

use Exception;
use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;




/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"player" = "Player", "coach" = "Coach"})
 * @UniqueEntity("identification")
 * @UniqueEntity("email")
 */

abstract class Person
{
    const ERR_GENERIC_SALARY = "Salario no valido."; 
    const ERR_CLUB_NOT_EXIST ="El club no existe.";
    const ERR_PERSON_NOT_EXIST = "Persona no existe";
    const CREATE_SUBJECT="Alta en el club";
    const DELETE_SUBJECT="Baja del club";    
    const ERR_GENERIC_UNSUSCRIBE = "Error al darse de baja"; 
       
    
    /**
     * @var int  
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Type("int")
     * @Serializer\Groups({"all", "person"})
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Nombre vacio")
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Nombre no puede tener menos {{ limit }} caracteres",
     *      maxMessage = "Nombre no puede tener más {{ limit }} caracteres"
     * )
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "person"})
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Positive(message="Edad valor positivo")
     * @Serializer\Type("int")
     * @Serializer\Groups({"all", "person"}) 
     */
    protected $age;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)     
     * @Assert\NotBlank(message="")
     * @Assert\Unique
     * @Assert\Length(min=9, max=9, exactMessage="")
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "person"})  
     */
    protected $identification;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Type("float")
     * @Serializer\Groups({"all", "person"})      
     */
    protected $salary=0;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     * @Assert\Unique
     * @Assert\Email(
     *     message = "Correo'{{ value }}' no valido."
     * )
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "person"})   
     */
    protected $email;
    
    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)     
     * @Serializer\Type("int")
     * @Serializer\Groups({"all", "person"})
     */
    protected $telephone;    
    
    /**
     * @var Club 
     * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="person")  
     * @Serializer\Type("App\Entity\Club") 
     * @Serializer\Groups({"all", "person"}) 
     */
    protected $club;
    
    
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

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }
    
    public function getIdentification(): string {
        return $this->identification;
    }
    
    public function setIdentification(string $identification): void {
        $this->identification = $identification;
    }      

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function setSalary(float $salary): self
    {
        $this->salary = $salary;

        return $this;
    }
    
    public function getClub(): ?Club {
        return $this->club;
    }
    
    public function setClub(?Club $club): void {
        $this->club = $club;
    } 
    
    public function getEmail() {
        return $this->email;
    }

    public function getTelephone() {
        return $this->telephone;
    }
    
    public function setEmail($email): void {
        $this->email = $email;
    }

    public function setTelephone($telephone): void {
        $this->telephone = $telephone;
    }

    public function checkSalary($total_salary,$salary): void{
        $rest_budget= $this->club->getBudget() - $total_salary;  
        
        if($salary < $rest_budget){           
            $this->setSalary($salary);                       
        }else{
            throw new Exception(self::ERR_GENERIC_SALARY);            
        }       
    }
}
