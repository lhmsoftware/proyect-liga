<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Jugador>
 *
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function add(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Search for club with parameters
     * @param string $condition
     * @param string $param
     * @param integer $page
     * @param integer $limit
     */
    public function findClubByCondition($condition, $param,$page,$limit)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder()
                        ->select(['pl'])
                        ->from('App:Player', 'pl')
                        ->Where($condition)            
                        ->setParameters($param) 
                        ->setFirstResult($limit * ($page - 1))
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();        
     
        return $query;      
    }
    
    /**
     * @param integer $page
     * @param integer $limit
     */
    public function allPlayer($page,$limit){
        
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder()
                        ->select(['pl'])
                        ->from('App:Player', 'pl')
                        ->setFirstResult($limit * ($page - 1))
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();        
        return $query;        
    } 

}
