<?php

namespace AwsSesWrapper\Test;

use Aws\Result;


/**
 * Class AwsSesWrapperTestCase 
 */
abstract class AwsSesWrapperTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockHandler 
     */
    protected $mock;
    
    /**
     * @var AwsSesWrapper
     */
    protected $client;


    public function testSetup() 
    {
        $this->assertEquals("eu-west-1", $this->client->getSesClient()->getConfig("signing_region"));
        $this->assertFalse($this->client->isAsync());
        $this->assertEquals("UTF-8", $this->client->getCharset());
    }
        
    public function testSendEmailSync()
    {
        $this->mock->append(new Result());
        $this->client->setTags(["id" => "6023"]);
        $result = $this->client->sendEmail(
            ["to" => ["mailtester@francescogabbrielli.it"]],
            "Test Simple Email",
            "<h1>Hello</h1><p>This is a fake test</p>",
            "Hello,\n this is a fake test");
        $this->assertInstanceOf(Result::class, $result);
    }
    
}