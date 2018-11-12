<?php

namespace AwsSesWrapper\Test;

use AwsSesWrapper\Test\AwsSesWrapperTestCase;

use AwsSesWrapper\AwsSesWrapper;
use Aws\Command;
use Aws\MockHandler;
use Aws\Result;
use Aws\Ses\Exception\SesException;

/**
 * Class AwsSesWrapperTest for V3 Api
 */
class AwsSesWrapperTest extends AwsSesWrapperTestCase
{
    public function setUp()
    {
        putenv("HOME=".__DIR__."/..");
        $this->mock = new MockHandler();
        $this->client = 
                AwsSesWrapper::factory("eu-west-1", "mail-tester", "Config1", $this->mock)
                    ->setFrom("MailTester <mailtester@francescogabbrielli.it>")
                    ->setCharset("UTF-8");
    }
    
    public function testGetTemplateExistingSync() 
    {
        $template = json_decode(file_get_contents(__DIR__."/template.json"), true);
        $this->mock->append(new Result(["Template" => $template]));
        $ret = $this->client->getTemplate("Template");
        $this->assertArrayHasKey("TemplateName", $ret->get("Template"));
    }
    
    public function testGetTemplateNotExistingAsync() 
    {
        $template = json_decode(file_get_contents(__DIR__."/template.json"), true);
        $this->mock->append(new SesException("Template does not exist", new Command("GetTemplate")));
        $ret = $this->client->setAsync(true)->getTemplate("Template")->then(
            function($result) {
                $this->assertNull($res);//must not enter here!
            },
            function($reason) {
                $this->assertInstanceOf(SesException::class, $reason);
                return "Failed";
            }
        )->wait();
        $this->assertEquals("Failed", $ret);
    }
    
    public function testGetTemplateForceAsync()
    {
        $template = json_decode(file_get_contents(__DIR__."/template.json"), true);
        $this->mock->append(function ($cmd, $req) {
            return new SesException('Template does not exist', $cmd);
        });
        $this->mock->append(new Result());
        $this->client->setAsync(true)->getTemplate("Template", $template)->then(
            function($result) {
                $this->assertInstanceOf(Result::class, $result);
            }, 
            function($reason) {
                $this->assertNull($reason);//must not enter here!
            }
        )->wait();
    }
    
    public function testGetTemplateForceSync() 
    {
        $template = json_decode(file_get_contents(__DIR__."/template.json"), true);
        $this->mock->append(function ($cmd, $req) {
            return new SesException('Template does not exist', $cmd);
        });
        $this->mock->append(new Result());
        $result = $this->client->getTemplate("Template", $template);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertInternalType('array', $result->get("@metadata"));
    }
    
    public function testDeleteTemplateSync()
    {
        $this->mock->append(new Result());
        $result = $this->client->deleteTemplate("Template");
        $this->assertInstanceOf(Result::class, $result);
    }
    
    public function testSendTemplatedEmailSync()
    {
        $this->mock->append(new Result());
        $this->client->setTags(["id" => "6023"]);
        $result = $this->client->sendTemplatedEmail(
            ["to" => ["mailtester@francescogabbrielli.it"]],
            "Template",
            ["name" => "Francesco", "animal" => "Dog"]);
        $this->assertInstanceOf(Result::class, $result);
    }    
    
    public function testSendBulkTemplatedEmailSync()
    {
        $this->mock->append(new Result());
        $this->client->setTags(["id" => "6023"]);//default tags
        $this->client->setData(["animal" => "Cat"]);//default data
        $result = $this->client->sendBulkTemplatedEmail([[
            "dest" => ["to" => ["mailtester@francescogabbrielli.it"]],
            "data" => ["name" => "Francesco", "animal" => "Dog"],
            "tags" => ["id" => "6023/1"]
        ]], "Template");
        $this->assertInstanceOf(Result::class, $result);
    }
    
}