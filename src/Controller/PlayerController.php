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
use App\Entity\Player;
use App\Entity\Person;
use App\Entity\Club;
use App\Classes\APIResponse;
use App\Utils\FilterLiga;
use OpenApi\Annotations as OA;
use App\Utils\LigaMessage;


class PlayerController extends AbstractController
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
     * @Route("/liga/player/add", name="add_player",methods={"POST"})
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
     * @OA\Tag(name="Player")
     */
    public function addPlayer(Request $request,LigaMessage $message)
    {
        $em = $this->getDoctrine()->getManager();
        $data_player = json_decode($request->getContent(), true);      

        try {
                       
            $player = new Player();            
            $player->setName($data_player['name']);
            $player->setAge($data_player['age']);
            $player->setIdentification($data_player['identification']);
            $player->setEmail($data_player['email']);
            $player->setTelephone($data_player['telephone']);          
            
            if(isset($data_player['club_id'])){
                
                $club = $em->getRepository(Club::class)->find($data_player['club_id']); 
                
                if(!empty($club)){
                    
                 $player->setClub($club); 
                 $total_salary=$em->getRepository(Person::class)->sumSalary($data_player['club_id']);
                 $player->checkSalary($total_salary[0]['total'], $data_player['salary']);  
                 
                }else{
                    throw new Exception(Player::ERR_CLUB_NOT_EXIST);
                }              
            }
            
            $player->setNumberShirt($data_player['number_shirt']);
            $player->setPositions($data_player['position']);
            $player->setHeight($data_player['height']);
            $player->setWeight($data_player['weight']); 
            $em->persist($player);
            $em->flush();

            $this->code = Response::HTTP_OK;
            $this->data = $player->getId();
            $this->error = false;
            
            //SEND MESSAGE
            $type_notification = $data_player['notification'];                
            $body= $this->renderView('notification/notificationBaja.html.twig',                         
                [
                    'type_person'=>'Player',                   
                    'name_person'=>$player->getName(),
                    'name_club'=> $player->getClub()->getName(),
                    'name_manager'=> $player->getClub()->getNameManager()

                ]); 
            
            //***NOTE***: Commented sendMessage because response is: Expected response code 220 but got empty response  
           // $message->sendMessaje($type_notification,$player->getEmail(), Player::CREATE_SUBJECT,$body);   
                              
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
            $this->data =  $ex->getMessage();           
        }
        
        $response = new APIResponse($this->error, $this->data);        
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    } 
       
    /**
     * @Route("/liga/player/unsubscribe/{player_id}",name="unsubscribe_player",methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="player_id",
     *     in="path",
     *     description="Player Id",
     *     @OA\Schema(type="integer")        
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Unsubscribe player.",     
     * )     
     * 
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to unsubscribe player.",
     * )
     *
     * @OA\Tag(name="Player")
     */
    public function unsubscribePLayer($player_id,Request $request,LigaMessage $message): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_player = json_decode($request->getContent(), true);  
        
        try {    
                      
            $player = $em->getRepository(Player::class)->find($player_id); 
            
            if(!empty($player) && !empty($player->getClub())){
                
                $name_club=$player->getClub()->getName();
                $manager=$player->getClub()->getNameManager();
                
                $player->setClub(null);  
                $player->setSalary(0);
                $em->persist($player);
                $em->flush();
                
                //SEND EMAIL
                $type_notification = isset($data_player['notification'])?$data_player['notification']:1;                   
                $body= $this->renderView('notification/notificationBaja.html.twig',   
                        [
                            'type_person'=>'Entrenador',                        
                            'name_person'=>$player->getName(),
                            'name_club'=> $name_club,
                            'name_manager'=> $manager

                        ]);                 
                //***NOTE***: Commented sendMessage because response is: Expected response code 220 but got empty response 
               //$message->sendMessaje($type_notification,$coach->getEmail(), Coach::CREATE_SUBJECT,$body);    

                $this->code = Response::HTTP_OK;
                $this->data = true;
                $this->error = false;                
                
            }else{
                throw new Exception(Player::ERR_GENERIC_UNSUSCRIBE);                
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
        
        $response = new APIResponse($this->error, $this->data);       
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    }
    
    /**
     * @Route("/liga/player/list", name="list_player",methods={"GET"})
     * 
     *
     * @OA\Response(
     *     response=200,
     *     description="List PLayer",     
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to list player.",     
     * )
     *
     * @OA\Tag(name="Player")
     */
    public function getListPlayer(Request $request,LigaMessage $message)
    {
     
        $em = $this->getDoctrine()->getManager();
        $player= $em->getRepository(Player::class);
        $data_player = json_decode($request->getContent(), true);
        
        try {
                    
            $page = isset($data_player['page'])?$data_player['page']:1;
            $limit = isset($data_player['limit'])?$data_player['limit']:5;
            
            if(isset($data_player['filter']) && !empty($data_player['filter'])){           
                
                $sql=FilterLiga::splitFilter($data_player['filter']);               
                $condition=$sql[0];
                $subquery = implode(' OR ', $condition);
                $param=$sql[1];                  
                $list=$player->findClubByCondition($subquery,$param,$page,$limit);    
                     
            
            }else{
                
                $list=$player->allPlayer($page,$limit);
            }  
            
            $this->code = Response::HTTP_OK;
            $this->error = false;
            $this->data = $list;
        }
        catch (Exception $ex) {
            $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->error = true;
            $this->data =  $ex->getMessage();  
            $this->logger->error($ex->getMessage());
        }         
        
        return new JsonResponse($this->serializer->serialize($this->data, "json"), $this->code, [], true);         
    }  
    
    /**
     * @Route("/liga/player/update-club/{player_id}",name="player_asign_club",methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="player_id",
     *     in="path",
     *     description="Player Id",
     *     @OA\Schema(type="integer")        
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Update club player",     
     * )     
     * 
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to update club player.",
     * )
     *
     * @OA\Tag(name="Player")
     */
    public function updatePlayerClub(Request $request,$player_id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data_player = json_decode($request->getContent(), true); 
        
        try {
            
            $club_id = $data_player['club_id'];        
            $salary = isset($data_player['salary']) ? $data_player['salary'] : $data_player->getSalary() ; 
            
            $player = $em->getRepository(Player::class)->find($player_id);            
            $club= $em->getRepository(Club::class)->find($club_id);            
            $total=$em->getRepository(Person::class)->sumSalary($club_id); 
            $total_salary =  $total[0]['total'];   
            $rest_budget= $club->getBudget() - $total_salary;  
            
            if($rest_budget>0 && $rest_budget>$salary){
               
                $player->getPerson()->setClub();
                $player->getPeson()->setSalary($salary);
            }          
          
            $em->persist($player);
            $em->flush();

            $this->code = Response::HTTP_OK;
            $this->data = true;
            $this->error = false;
            
            
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
        
        $response = new APIResponse($this->data, $this->error);        
        return new JsonResponse($this->serializer->serialize($response, "json"), $this->code, [], true);
    }
        
    /**
     * @Route("/liga/player/delete/{player_id}", name="player_delete",methods={"DELETE"})
     *
     * @OA\Parameter(
     *     name="player_id",
     *     in="path",
     *     description="Player Id",
     *     @OA\Schema(type="integer")     
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Delete player",
     *
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="An error has occurred trying to delete player.",
     * )
     *
     *
     * @OA\Tag(name="Player")
     */
    public function deletePlayer($player_id): Response
    {
        $em = $this->getDoctrine()->getManager();
     
        try {
            
            $player = $em->getRepository(Player::class)->find($player_id); 
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
