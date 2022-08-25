<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use App\Entity\Coach;
use App\Entity\Person;
use App\Entity\Club;
use OpenApi\Annotations as OA;
use App\Classes\APIResponse;
use App\Utils\LigaMessage;


class CoachController extends AbstractController
{    
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
     * @Route("/liga/coach/add",name="add_coach",methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Coach created",     
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to create coach.",     
     * )
     *
     * @OA\Tag(name="Coach")
     */
    public function addCoach(Request $request, LigaMessage $message)
    {
        $em = $this->getDoctrine()->getManager();
        $data_coach = json_decode($request->getContent(), true);      

        try {
            
            $coach = new Coach();           
            $coach->setName($data_coach['name']);
            $coach->setAge($data_coach['age']);
            $coach->setIdentification($data_coach['identification']);
            $coach->setEmail($data_coach['email']);
            $coach->setTelephone($data_coach['telephone']);          
            
            if(isset($data_coach['club_id'])){
                
                $club = $em->getRepository(Club::class)->find($data_coach['club_id']); 
                
                if(!empty($club)){
                    
                 $coach->setClub($club);                  
                 $total_salary=$em->getRepository(Person::class)->sumSalary($data_coach['club_id']);                  
                 $coach->checkSalary($total_salary[0]['total'], $data_coach['salary']);          
                 
                }else{
                    
                    throw new Exception(Coach::ERR_CLUB_NOT_EXIST);
                }              
            }            
            
            $coach->setExperience($data_coach['experience']);
            $coach->setTypeCoach($data_coach['type_coach']);           
            $em->persist($coach);
            $em->flush();           
            
            //SEND MESSAGE            
            $type_notification = isset($data_coach['notification'])?$data_coach['notification']:1;       
            $body= $this->renderView('notification/notificationAlta.html.twig',                    
                 [
                     'type_person'=>'Entrenador',                 
                     'name_person'=>$coach->getName(),
                     'name_club'=> $coach->getClub()->getName(),
                     'name_manager'=> $coach->getClub()->getNameManager()

                 ]); 
            
             //***NOTE***: Commented sendMessage because response is: Expected response code 220 but got empty response         
            //$message->sendMessaje($type_notification,$coach->getEmail(), Coach::CREATE_SUBJECT,$body);
           
            $this->code = Response::HTTP_OK;
            $this->data = $coach->getId();
            $this->error = false; 
            
           
        
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
     * @Route("/liga/coach/unsubscribe/{coach_id}",name="unsubscribe_coach",methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="coach_id",
     *     in="path",
     *     description="Coach Id",
     *     @OA\Schema(type="integer")        
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Unsubscribe coach",     
     * )     
     * 
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to unsubscribe coach.",
     * )
     *
     * @OA\Tag(name="Coach")
     */
    public function unsubscribeCoach($coach_id,Request $request,LigaMessage $message): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_coach = json_decode($request->getContent(), true);  
        
        try {     
                      
            $coach = $em->getRepository(Coach::class)->find($coach_id); 
            
            if(!empty($coach) && !empty($coach->getClub())){
                
                $name_club=$coach->getClub()->getName();
                $manager=$coach->getClub()->getNameManager();
                
                $coach->setClub(null);   
                $coach->setSalary(0);
                $em->persist($coach);
                $em->flush();            
                
                //SEND EMAIL
                $type_notification = isset($data_coach['notification'])?$data_coach['notification']:1;                   
                $body= $this->renderView('notification/notificationBaja.html.twig',                         
                    [
                        'type_person'=>'Entrenador',                        
                        'name_person'=>$coach->getName(),
                        'name_club'=> $name_club,
                        'name_manager'=> $manager

                    ]); 
                
                //***NOTE***: Commented sendMessage because response is: Expected response code 220 but got empty response 
               //$message->sendMessaje($type_notification,$coach->getEmail(), Coach::CREATE_SUBJECT,$body);     
                
                $this->code = Response::HTTP_OK;
                $this->data = true;
                $this->error = false;
                
                
            }else{
                throw new Exception(Coach::ERR_GENERIC_UNSUSCRIBE);                
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
            $this->data=$ex->getMessage();           
        }
        
        $response = new APIResponse($this->error,$this->data);  
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    } 
    
    /**
     * @Route("/liga/coach/update-club/{coach_id}",name="update_club_coach",methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="coach_id",
     *     in="path",
     *     description="Coach Id",
     *     @OA\Schema(type="integer")        
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="",     
     * )     
     * 
     * @OA\Response(
     *     response=500,
     *     description="",
     * )
     *
     * @OA\Tag(name="Coach")
     */
    public function updateClubCoach(Request $request,$coach_id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_coach = json_decode($request->getContent(), true); 
        
        try {           
         
           if(isset($data_coach['club_id']) && !empty($data_coach['club_id'])){
               
               
               $coach = $em->getRepository(Coach::class)->find($coach_id);
               
               $club_id = $data_coach['club_id'];        
               $salary = isset($data_coach['salary']) ? $data_coach['salary'] : $coach->getSalary() ;               

               $club= $em->getRepository(Club::class)->find($club_id);            
               $total=$em->getRepository(Person::class)->sumSalary($club_id); 
               $total_salary =  $total[0]['total'];   
               $rest_budget= $club->getBudget() - $total_salary;   

                if($rest_budget>0 && $rest_budget>$salary){

                    $coach->setClub($club);
                    $coach->setSalary($salary);            
                }          

                $em->persist($coach);
                $em->flush();

                $this->code = Response::HTTP_OK;
                $this->data = true;
                $this->error = false;

           }else{
               
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
            $this->data=$ex->getMessage();           
        }
        $response = new APIResponse($this->error,$this->data);        
   
       return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    }
    
     /**
     * @Route("/liga/coach/delete/{coach_id}", name="coach_delete",methods={"DELETE"})
     *
     * @OA\Parameter(
     *     name="coach_id",
     *     in="path",
     *     description="Player Id",
     *     @OA\Schema(type="integer")     
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Delete coach",
     *
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to delete coach.",
     * )
     *
     *
     * @OA\Tag(name="Coach")
     */
    public function deletePlayer($coach_id): Response
    {
        $em = $this->getDoctrine()->getManager();
     
        try {
            
            $player = $em->getRepository(Coach::class)->find($coach_id); 
            $em->remove($player);
            $em->flush();
            
            $this->code = Response::HTTP_OK;
            $this->data = true;
            $this->error = false;       
        }
        catch (Exception $ex) {
            $this->error = true;
            $this->logger->error($ex->getMessage());
            $this->code = Response::HTTP_OK;
            $this->data=false;          
        }
        
        $response = new APIResponse($this->error,$this->data,);        
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);        
    }   
    
    

}
