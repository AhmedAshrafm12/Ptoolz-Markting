<?php
namespace tests\unit;
use modules\clients;
use modules\database;
use modules\Dep;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase{
   protected  \pdo $db ;
    protected function setUp():void
    {
        // include "includes/connect.php";
        require 'vendor/autoload.php';
        include 'modules/database.php';
        include 'modules/clients.php';
        include 'modules/Dep.php';
        parent::setUp();
    }
    
    /**
     * @test
     */
    public function prccess(){
        $depMock = $this->createMock(\modules\Dep::class);
        $client = new clients($this->db , $depMock);
       $depMock->method('success')->willReturn(true);
     
        $data = $client->index();

        // $excpected = '4';
        
        $this->assertTrue($data);
         
    }
    /**
     * @test
     */
    public function email_send(){
        $this->db = (new database())->connect();
        $depMock = $this->createMock(\modules\Dep::class);
        $client = new clients($this->db , $depMock);
       $depMock->method('success')->willReturn(true);
      
       $depMock->expects($this->once())->method('send_email')->with(['name'=>'ahmedd' , 'msg'=>'hello'] , 'test');
        $data = $client->index();

        // $excpected = '4';
        
        $this->assertTrue($data);
         
    }


}