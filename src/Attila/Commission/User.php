<?php namespace Attila\Commission;

class User 
{

  public $id;
  public $operationsByWeek = [];
  
  public static $storage = [];

  public function __construct(int $id)
  {
    $this->id = $id;

  }

  public static function get(int $id): User
  {
    if(array_key_exists($id, User::$storage)) {
      return User::$storage[$id];
    }

    return User::create($id);
    
  }

  public static function create(int $id): User
  {
 
    return User::$storage[$id] = new User($id);
     
  }

  public function operationsByWeekOfTheDate(\DateTime $date){
    $opWeek = date('oW', $date->getTimeStamp());

    if(array_key_exists($opWeek, $this->operationsByWeek)){
      return $this->operationsByWeek[$opWeek];
    }
    return [];
  }
  public function addOperation(&$operation)
  {
    $opWeek = date('oW', $operation->date->getTimeStamp());

    if(array_key_exists($opWeek, $this->operationsByWeek)){
      array_push($this->operationsByWeek[$opWeek], $operation);
      return $this->operationsByWeek[$opWeek];
    }
    return $this->operationsByWeek[$opWeek] = [$operation];
  }
}