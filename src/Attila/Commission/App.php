<?php namespace Attila\Commission;

class App {

  public $dataFile;

  public $operations = [];
  

  public function __construct(string $dataFile)
  {
    $this->dataFile = $dataFile;
  }

  public function init()
  {

    $this->reader = new Reader($this->dataFile);
    $this->reader->read();

    $out = fopen('php://output', 'wb'); //output handler
    
    foreach($this->reader->lines as $lineOfData) {
      $op = new Operation($lineOfData);
      fputs($out, $op->calculate());
      fputs($out, "\n");
    }
    
    fclose($out);
  }
}