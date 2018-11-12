<?php

namespace AwsSesWrapper\Test;

use AwsSesWrapper\Test\AwsSesWrapperTestCase;

use AwsSesWrapper\AwsSesWrapper;
use Aws\MockHandler;

/**
 * Class AwsSesWrapperTest for V2 Api
 */
class AwsSesWrapperTest extends AwsSesWrapperTestCase
{
    public function setUp()
    {
        putenv("HOME=".__DIR__);
        $this->mock = new MockHandler();
        $this->client = 
                AwsSesWrapper::factory("eu-west-1", "mail-tester", null, $this->mock)
                    ->setFrom("MailTester <mailtester@francescogabbrielli.it>")
                    ->setCharset("UTF-8");
    }
    
}