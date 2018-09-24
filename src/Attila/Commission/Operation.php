<?php namespace Attila\Commission;

class Operation {

  const IN = 1;
  const OUT = 2;
  const CHECK_IN_COMMISSION_FEE = 0.03;
  const CHECK_IN_MAX_FEE = 500;  // in cents
  const CHECK_OUT_FEE = 0.3;
  const LEGAL_CHECK_OUT_MIN_FEE = 50;  // in cents
  const WEEKLY_MAX_DISCOUNTED_OP_LIMIT = 3;
  const FREE_OF_CHARGE = 100000; // in cents
  const OP_TYPES = array(
    'cash_in' => 1,
    'cash_out' => 2
  );
  const CONVERSION_RATES = array(
    "USD" => 1.1497,
    "JPY" => 129.53
  );
  const NATURAL = 1;
  const LEGAL = 2;
  const PAYMENT_TYPES = array(
    "natural" => 1,
    "legal" => 2
  );

  //2016-01-05,1,natural,cash_in,200.00,EUR
  public function __construct($lineOfData)
  {
    $this->date = \DateTime::createFromFormat('Y-m-d', $lineOfData[0]);
    
    $this->user = User::get($lineOfData[1]);

    if(!array_key_exists($lineOfData[2], Operation::PAYMENT_TYPES)) {
      throw new \InvalidArgumentException('Unsupported user type, Please check your data file.');
    }
    
    $this->paymentType = Operation::PAYMENT_TYPES[$lineOfData[2]];

    if(!array_key_exists($lineOfData[3], Operation::OP_TYPES)) {
      throw new \InvalidArgumentException('Unsupported operation type. Please check your data file.');
    }
    
    $this->type = Operation::OP_TYPES[$lineOfData[3]];

    // For evading PHP's float point precision problem we are going to 
    // calculate everything as cents until last moment. There is no yen cent but 
    // for ease of naming we can go on. 
    $this->amount = (int)($lineOfData[4] * 100);

    if(!array_key_exists($lineOfData[5], Operation::CONVERSION_RATES) && $lineOfData[5] !== 'EUR') {
      throw new \InvalidArgumentException('Unsupported currency type. Please check your data file.');
    }

    $this->currency = $lineOfData[5];
    
  }

  public function calculate()
  {
    if($this->type === Operation::IN) { // check_in

      $this->commissionInCents = $this->amount * Operation::CHECK_IN_COMMISSION_FEE / 100;
      
      $this->commissionInCents = $this->commissionInCents > Operation::CHECK_IN_MAX_FEE ? 
        Operation::CHECK_IN_MAX_FEE : $this->commissionInCents;

    } else { // cache_out

      if ($this->paymentType === Operation::NATURAL) { // natural

        $operationsOfTheWeek = $this->user->operationsByWeekOfTheDate($this->date);
        
        if(count($operationsOfTheWeek) <= Operation::WEEKLY_MAX_DISCOUNTED_OP_LIMIT) {

          $this->calculateDiscountedInCents($operationsOfTheWeek);

        } else {

          $this->commissionInCents = $this->amount * Operation::CHECK_OUT_FEE / 100;
        
        }

      } else { // legal entity
        $this->commissionInCents = $this->amount * Operation::CHECK_OUT_FEE / 100;
        $this->commissionInCents = 
          $this->commissionInCents > Operation::LEGAL_CHECK_OUT_MIN_FEE ? 
          $this->commissionInCents : Operation::LEGAL_CHECK_OUT_MIN_FEE;
      }  
    }
    
    $this->user->addOperation($this);
    return $this->round();
  }

  private function calculateDiscountedInCents($operations)
  {

    $conversionRate = 1;

    $pastOpAmount = array_reduce($operations, function($tot, $op) {

      $oldOpConversationRate = 1;
      
      if($op->currency !== 'EUR') {
        $oldOpConversationRate = Operation::CONVERSION_RATES[$op->currency];
      }

      if($op->type === Operation::OUT && $op->paymentType === Operation::NATURAL) {
        $tot += $op->amount / $oldOpConversationRate;
      }
      return $tot;
    });

    if($this->currency !== 'EUR') {
      $conversionRate = Operation::CONVERSION_RATES[$this->currency];
    }

    $pastOpAmount = $pastOpAmount * $conversionRate;
    $free = Operation::FREE_OF_CHARGE * $conversionRate;
    $amount = $this->amount;
  
    $free -= $pastOpAmount;

    $amount = ($free <= 0 ) ? $amount : $amount - $free;
    $amount = ($amount <= 0 ) ? 0 : $amount;

    $this->commissionInCents = $amount * Operation::CHECK_OUT_FEE / 100;

  }

  private function round()
  {
    
    if ($this->currency === 'JPY') return ceil($this->commissionInCents / 100);
    return number_format(ceil($this->commissionInCents) / 100, 2, '.', '');
  }
}
