<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Club;

class ClubFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $club = new Club();
        $club->setName('Club Sevilla');
        $club->setNameManager("Pedro Perez");
        $club->setBudget(15000);    
        $manager->persist($club);
        
        $club1 = new Club();
        $club1->setName('Club Malaga');
        $club1->setNameManager("Juan Martinez");
        $club1->setBudget(50);    
        $manager->persist($club1);
        
        $club2 = new Club();
        $club2->setName('Club Madrid');
        $club2->setNameManager("Manuel Hernandez");
        $club2->setBudget(500);    
        $manager->persist($club2);
        
        $club3 = new Club();
        $club3->setName('Club Barcelona');
        $club3->setNameManager("Hector Rodriguez");
        $club3->setBudget(1);    
        $manager->persist($club3);    
        
        $club4 = new Club();
        $club4->setName('Club Getafe');
        $club4->setNameManager("Pedro Pupo");
        $club4->setBudget(1);    
        $manager->persist($club4);       
               
        
        $manager->flush();
    }

       
    
}
