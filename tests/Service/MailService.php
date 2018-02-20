<?php
    // tests/Service/MailService.php
    namespace App\Tests\Util;

    use App\Service\MailService;
    use PHPUnit\Framework\TestCase;

    class MailServiceTest extends TestCase
    {

        // Run function and check if true
        public function testEmptyQueue()
        {
            $Mail = new MailService();
            $result = $Mail->emptyQueue();
            $this->assertEquals(true, $result);
        }
    }
