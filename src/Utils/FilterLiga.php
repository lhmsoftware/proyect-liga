<?php

namespace App\Utils;



class FilterLiga
{

    /**
     * Filter split param and condition
     *
     * @param array $data
     * @param array $criteria
     * @return array|null
     */
    public static function splitFilter(array $filters): ?array
    {

        $newData = [];
        foreach ($filters as $key => $val) {
                 if (FilterLiga::checkFilter($val,$key)) {
                $condition[] = 'pl.' . $key . ' LIKE :' . $key;
                $param[$key] = $val . '%';
            }
           }
           array_push($newData,$condition,$param);            
        return $newData;
    }
    
    
    /**
     * Check key and value 
     *
     * @param  $value
     * @param string $key 
     * @return boolean    
     */    
    public static function checkFilter($value,$key){
     
        switch ($key){
            
            case "name":           
            case "age":
            case "identification":
            case "salary":
            case "email":
            case "telephone":    
                $result=true;
                break;
            default:
                $result=false;                
        }
        
        if(empty($value)){
            $result=false;
        }   
        
        return $result;
    }
    
    /**
     * @param array $paramList
     * @param string $root
     * @return array
     */
    public static function filterParams(array $paramList, string $root = 'APP'): array
    {
        $values = [];
        foreach ($paramList AS $p) {
            //exists the complete name: prefix + name
            $pr = $root . '_' . $p;
            if ($_ENV[$pr] !== false) {
                //the key will be the last group
                $re = '/(.+)_([A-Z0-9]+)$/i';
                if (preg_match($re, $pr, $matches)) {
                    $values[strtolower($matches[2])] = $_ENV[$pr];
                }
            }
        }
        return $values;
    }


}
