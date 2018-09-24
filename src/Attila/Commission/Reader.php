<?php namespace Attila\Commission;

class Reader 
{

  public $lines = [];

  public function __construct(string $fileName)
  {
    $this->fileName = $fileName;
  }

  public function read(){

    $dt = fopen($this->fileName, 'rb');

    if($dt === false) {
        throw new \InvalidArgumentException('File couldn\'t read. Please check the data file.');
    }
    
    $lineNo = 1;
    
    while(($line = fgetcsv($dt, 0, ',')) !== false ) {

      if (count($line) !== 6) {
        throw new \InvalidArgumentException("Field number is wrong at line: $lineNo. Please check the data file.");
      }
      $lineNo++;
      array_push($this->lines, $line);
    }
    
    fclose($dt);
   
  }
}