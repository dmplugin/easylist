<?php
/**
 * @package EasyList
 */
namespace EasyList;

use Exception;
use PDO;
use PDOException;
use DateTime;
use EasyList\Exceptions\EasyListException;

class ListFilter
{
    /**
     * @param String $condition
     * @return String
     * Description : Remove quotes before question marks
     */
    private function CeanQuotes($condition){
        $result = $condition;
        
        $pattern = "/'\s*\?\s*'/i";
        $result = preg_replace($pattern, '?', $result);
        $pattern = '/"\s*\?\s*"/i';
        $result = preg_replace($pattern, '?', $result);
        
        return $result;
    }
    
    /**
     * 
     * @param array $eachfilter
     * @param array $methodArray
     * @throws EasyListException
     * @return string
     */
    public function filter($eachfilter, $methodArray){
            
            $subfilter = "";
            $subValues = "";
            
            $condition = isset($eachfilter['condition']) ? $eachfilter['condition'] : "";
            $postVariable = !isset($eachfilter['form-field']) ? "" : (isset($methodArray[$eachfilter['form-field']]) ? $methodArray[$eachfilter['form-field']] : "");
            $operation = isset($eachfilter['operation']) ? $eachfilter['operation'] : "";
            $type = isset($eachfilter['type']) ? trim(strtoupper($eachfilter['type'])) : "STRING";
            $dateFormatFrom = isset($eachfilter['datetime_format_from']) ? trim($eachfilter['datetime_format_from']) : "Y-m-d";
            $dateFormatTo = isset($eachfilter['datetime_format_to']) ? trim($eachfilter['datetime_format_to']) : "Y-m-d";
            $empty_consider = isset($eachfilter['consider_empty']) ? trim(strtoupper($eachfilter['consider_empty'])) : "NO";
            
            if($condition && ($postVariable === "0" ||$postVariable || $empty_consider == "YES")){
                $clean_filter = $this->CeanQuotes($condition);
                $ary_subfilter = explode("?", $clean_filter);
                
                $subfilter = $ary_subfilter[0] . " ";
                
                if($type != "ARRAY" && is_array($postVariable)){
                    $postVariable = $postVariable[0];
                }
                
                if(!is_array($postVariable)){
                    $postVariable = trim($postVariable);
                }

                switch($type){
                    case 'BOOLEAN' :
                        if($postVariable != ""){
                            $subfilter .= "{$postVariable}";
                        } else {
                            $subfilter .= "0";
                        }
                        break;
                    case 'DATETIME' :
                    case 'DATE' :
                    case 'TIME' :
                        $dateObj = DateTime::createFromFormat($dateFormatFrom, $postVariable);
                        if($dateObj){
                            $new_date = $dateObj->format($dateFormatTo);
                            $subfilter .= "'{$new_date}'";
                        } else {
                            throw new EasyListException("Date not matching with the 'datetime_format_from'. ");
                            $subfilter .= "''"; 
                        }
                        break;
                    case 'STRING' :
                        if(stripos($ary_subfilter[0], " LIKE ") !== false){
                            $subfilter = trim($subfilter);
                            $subfilter .= $postVariable;
                        } else {
                            $subfilter .= "'" . $postVariable . "'";
                        }
                        break;
                    case 'ARRAY' :
                        if(is_array($postVariable)){
                            $subValues = "'" . implode("','", $postVariable) . "'";
                            $subfilter .= $subValues;
                        } else {
                            $subfilter .= "'" . $postVariable . "'";
                        }
                        break;
                }
                
                $subfilter .= $ary_subfilter[1] . " " . $eachfilter["operation"] . " ";
            }
            
            return $subfilter;
    }
    
}
