<?php 

use PHPUnit\Framework\TestCase;

final class ReaderTest extends TestCase 
{
  /**
   * @expectedException TypeError
   */
  public function testReaderClassThrowWhenThereIsNoArgument()
  {
  
    $reader = new Attila\Commission\Reader();
  
  }

  public function testReadMethodShouldExist()
  {
  
    $reader = new Attila\Commission\Reader("dummy");
    $this->assertTrue(method_exists($reader, 'read'));
  
  }
  /**
   * @expectedException ErrorException
   */
  public function testReadMethodsThrowWhenFileNotFound()
  {
  
    $reader = new Attila\Commission\Reader("dummy");
    $reader->read();
    
  }
  public function testReadMethod()
  {
    $reader = new Attila\Commission\Reader(__DIR__ . DIRECTORY_SEPARATOR . 'valid-test-input.csv');
    $reader->read();
    $this->assertEquals(array(array('2016-01-06','1','natural','cash_out','30000','JPY')), $reader->lines);
  }
  /**
   * @expectedException InvalidArgumentException
   */
  public function testReadMethodWithInvalidCVS()
  {
    
    $reader = new Attila\Commission\Reader(__DIR__ . DIRECTORY_SEPARATOR . 'invalid-test-input.csv');
    $reader->read();
    
  }
}
