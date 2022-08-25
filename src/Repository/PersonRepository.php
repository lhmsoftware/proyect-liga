<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function add(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function sumSalary(int $club_id){        
        try {
            
            $em = $this->getEntityManager();
            $query = $em->createQueryBuilder()
                            ->select('SUM(p.salary) as total')                           
                            ->from('App:Person', 'p')  
                            ->innerJoin('p.club', 'club')
                            ->andWhere('club.id = :id')
                            ->setParameter('id', $club_id)
                            ->getQuery()->getResult();
            
            return $query;
        }
        catch (Exception $ex) {
            throw $ex;
        }

    }   


}
