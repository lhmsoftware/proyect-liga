<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Club;
use App\Controller\ClubController;

class ClubControllerTest extends WebTestCase
{
     /** @test */
    public function upadteBudgetTest()
    {
        $client = static::createClient();   
          
        $testBodies = [
            ["bud" => "5000", "exceptionCode" => ClubController::ERR_MISSING_DATA], //missing keys data
            ["budget" => "0", "exceptionCode" => ClubController::ERR_BUDGET_NULL], //incorrect input data = 0
            ["budget" => "50", "exceptionCode" => ClubController::ERR_BUDGET_LITTLE],//incorrect input data<total          
            ["budget" => "90000", "exceptionCode" => ClubController::OK_CODE] //ok
        ];

        foreach($testBodies as $testBody)
        {
            $response = $client->request('PUT', '/liga/club/update/1', ['form_params' => $testBodies]);
            $this->assertInstanceOf(Club::class, $response);
            $this->assertEquals($testBody['exceptionCode'], $response->data);
        }     
       
    }
}