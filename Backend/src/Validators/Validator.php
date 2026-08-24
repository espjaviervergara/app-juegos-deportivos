<?php
namespace App\Validators;
class Validator {
  public static function required(array $data, array $fields): ?array {
    $errs=[];
    foreach($fields as $f) if(empty($data[$f])) $errs[]=['field'=>$f,'message'=>'required'];
    return $errs?:null;
  }
  public static function paginate(int $page,int $limit): array { return [max(1,$page), min(100,max(1,$limit))]; }
}
