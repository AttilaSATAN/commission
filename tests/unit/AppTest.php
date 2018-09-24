<?php 

use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase 
{
  /**
   * @expectedException TypeError
   */
  public function testAppThrowWhenThereIsNoArgumentsGiven()
  {
  
    $app = new Attila\Commission\App();
  }

  public function testAppCastsArgumentToString()
  {
    $app = new Attila\Commission\App(4);
    $this->assertSame('4', $app->dataFile);
  }
  public function testAppHasInitMethod()
  {
    $app = new Attila\Commission\App('arg');

    $this->assertTrue(method_exists($app, 'init'));
  }
  
}