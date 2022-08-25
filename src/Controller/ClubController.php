<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Club;
use App\Classes\APIResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use App\Entity\Person;





class ClubController extends AbstractController
{
   const ERR_GENERIC_BUDGET ="Error en el presupuesto";    
    
    //Internal vars
    private $code;
    private $error;
    private $data;
    private $logger;
    private $serializer;
    
    /*
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(LoggerInterface $logger,SerializerInterface $serializer)
    {
      $this->logger = $logger;
      $this->serializer = $serializer;
    }
    
    
     /**
     * @Route("/liga/club/add", name="add_club",methods={"POST"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Cub created",     
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to create club.",     
     * ) 
     *
     * @OA\Tag(name="Club")
     */
    public function addClub(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_club = json_decode($request->getContent(), true);      

        try {
                      
            $club = new Club();
            $club->setName($data_club['name']);
            $club->setNameManager($data_club['name_manager']);
            $club->setBudget($data_club['budget']);         
                      
            if($club->getBudget()>0){
                
                $em->persist($club);
                $em->flush();

                $this->code = Response::HTTP_OK;
                $this->data = $club->getId();
                $this->error = false;  
                    
            }else{
                throw new Exception(self::ERR_GENERIC_BUDGET); 
            }        
            
        }catch (ValidationException $ex) {
            $this->code = Response::HTTP_OK;
            $this->logger->error($ex->getMessage());
            $this->error = true;
            $this->data = $ex->getValidations();
        }              
        catch (Exception $ex) {
            $this->code = Response::HTTP_OK;
            $this->logger->error($ex->getMessage());
            $this->error = true;
            $this->data = $ex->getMessage();         
          }
   
        $response = new APIResponse($this->error, $this->data);     
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    }
    
    
     /**
     * @Route("/liga/club/update/{club_id}",name="update_budget",methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="club_id",
     *     in="path",
     *     description="Club Id",
     *     @OA\Schema(type="integer")        
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Update Club",     
     * )     
     * 
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to update budget.",
     * )
     *
     * @OA\Tag(name="Club")
     */
    public function upadteBudget(Request $request,$club_id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_club = json_decode($request->getContent(), true); 
        
        try {        
            
            $total=$em->getRepository(Person::class)->sumSalary($club_id);   //suma de todos los salarios del club     
            $total_salary =  $total[0]['total'];
          
            $budget = $data_club['budget'];
            
            if($budget > 0 && $budget > $total_salary){              
                                       
                $club = $em->getRepository(Club::class)->find($club_id);
                $club->setBudget($budget);   
                $em->persist($club);
                $em->flush();

                $this->code = Response::HTTP_OK;
                $this->data = true;
                $this->error = false;
                
            }else {
                
                throw new Exception(self::ERR_GENERIC_BUDGET); 
            }
            
        }catch (ValidationException $ex) {
            $this->code = Response::HTTP_OK;
            $this->logger->error($ex->getMessage());
            $this->error = true;
            $this->data = $ex->getValidations();
        }
        catch (Exception $ex) {
            $this->error = true;
            $this->logger->error($ex->getMessage());
            $this->code = Response::HTTP_OK;
            $this->data = $ex->getMessage();           
        }
        
        $response = new APIResponse($this->error,$this->data);  
       
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    }
       
}
