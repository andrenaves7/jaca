<?php

use PHPUnit\Framework\TestCase;
use Jaca\Core\Application;

class ExampleTest extends TestCase
{
    public function testAppRuns()
    {
        $app = new Application();
        $this->assertInstanceOf(Application::class, $app);
    }
}
