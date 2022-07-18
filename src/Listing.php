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

class Listing
{
    public static $connection;
    public static $protocol;
    
    /**
     * Creates Connection
     */
    public static function Connection()
    {
        if(!self::$connection){
            $conn = new ListConnection();
            self::$connection = $conn->setConnection();
            self::$protocol = $conn->getProtocol();
        } 
    }
    
    /**
     * @param array $options
     * $options array 
     * array(
         "select" 	             => "<Comma separated column list>"
        ,"from" 	             => "<From table with alias>"
        ,"joins" 	             => "<Join statements>"
        ,"conditions"            => array(
                        			array("condition" => "name = ?", "value" => "<FILTER-NAME>", "operation" => "AND" ),
                        			array("condition" => "age = ?", "value" => "<FILTER-AGE>", "operation" => "OR" ),
                        			array("condition" => "(name = ? AND age IN( ?) )", "value" => array(<FILTER-NAME>, <ARRAY-FILTER-AGE->)), "operation" => "OR" )
                        		  )
        ,"group" 	             => "<Comma separated group names>"
        ,"having" 	             => array(
                        			array("condition" => "name = ?", "value" => "<FILTER-NAME>", "operation" => "AND" ),
                        			array("condition" => "age = ?", "value" => "<FILTER-NAME>", "operation" => "OR" )
                        		  )
        ,"having_columns"        => "<Comma separated list of columns used in HAVING cluase. This is to prevent count query error when HAVING is used>"                         		  
                        		  //Note : Either filter or condition will be considered
       ,"filters"                 => array( 
                                    array("condition" => "alias.Column-name = ?", "form-field" => "<INPUT ELEMENT NAME OF FORM>", "operation" => "AND|OR", "type"=>"BOOLEAN|DATE|TIME|INTEGER|STRING", "datetime_format_from"=>"d/m/Y : Use php date format", "datetime_format_to"=>"d/m/Y", "consider_empty" => "YES|NO : Default - NO"),
                                    array("condition" => "alias.Column-name = ?", "form-field" => "<INPUT ELEMENT NAME OF FORM>", "operation" => "AND|OR", "type"=>"BOOLEAN|DATE|DATETIME|TIME|INTEGER|STRING", "datetime_format_from"=>"d/m/Y : Use php date format", "datetime_format_to"=>"PHP date format d/m/Y", "consider_empty" => "YES|NO : Default - NO"),
                                  )
        ,"order" 	             => "<Comma separated order coluns with ASC/DESC key >"
        ,"return_data"           => "<HTML / JSON / OBJECT / QUERY>"
        ,"view"	                 => "<view location if return_data is HTML>"
        ,"view_variables"        => array("variable"=>"$variableName" [...])
        ,"page" 	             => "<page number>"
        ,"pagination" 	         => "YES | NO - Default Yes"
        ,"page_size"             => "<page size>"
       )
     */
    public static function Page($options)
    {
        $sql                = "";
        $count_sql          = "";
        $select             = "";
        $query              = "";
        $viewData           = "";
        $subCondition       = "";
        $methodArray        = (!empty($_POST)) ? $_POST : $_GET;
        
        $return_data        = isset($options["return_data"]) ? $options["return_data"] : "JSON";
        $page_size          = isset($methodArray['page_size']) ? $methodArray['page_size'] : (!empty($options["page_size"]) && $options["page_size"] != 0  ? $options["page_size"] : 25);
        $page               = isset($methodArray['page']) ? $methodArray['page'] : (isset($options["page"]) ? $options["page"] : 1);
        $total_records      = isset($methodArray['total_records']) ? $methodArray['total_records'] : (isset($options["total_records"]) ? $options["page"] : 0);
        $pagination         = isset($options["pagination"]) ? $options["pagination"] : 'YES';
        $having_columns     = isset($options["having_columns"]) ? "," . trim($options["having_columns"],",") : '';
        
        $order              = isset($options["order"]) ? $options["order"] : "";
        $sort               = isset($methodArray['sort']) ? $methodArray['sort'] : "";
        $sort_type          = isset($methodArray['sort_type']) ? $methodArray['sort_type'] : "";
        
        $mainData = array(
             "page_size"       => $page_size
            ,"page"            => $page
            ,"total_records"   => $total_records
            ,"next_page"       => 1
            ,"prev_page"       => 1
            ,"last_page"       => 1
            ,"total_pages"     => 1
            ,'is_pagination'   => $pagination
            ,"return_data"     => $return_data
            ,"data"            => array()
        );
        
        $data = array();
        
        if(!isset($options['select']) || trim($options['select']) == "" || !isset($options['from']) || trim($options['from']) == "" ){
            throw new EasyListException("Select OR From clause is missing.");
        } else {
            $select = "SELECT " . $options['select'] . " ";
            $sql .= " FROM " . $options['from']  . " ";
        }
        
        if(isset($options['joins']) && $options['joins'] !=""){
            $sql .= $options['joins'];
        }
        
        //Condtion option will not consider if Filter option is present
        if(isset($options['filters']) && is_array($options['filters'])){
            $subCondition = self::ConditionBuilderForFilter($options['filters']);
            if(trim($subCondition) != ""){
                $sql .= " WHERE " .  $subCondition;
            }
        } elseif(isset($options['conditions']) && $options['conditions'] !=""){
            $subCondition = self::ConditionBuilder($options['conditions']);
            if(trim($subCondition) != ""){
                $sql .= " WHERE " .  $subCondition;
            }
        }
        
        if(isset($options['group']) && $options['group'] !=""){
            $sql .= " GROUP BY " .  $options['group'];
        }
        
        if(isset($options['having']) && $options['having'] !=""){
            $subCondition = self::ConditionBuilder($options['having']);
            $sql .=  " HAVING " . $subCondition;
        }
        
        //Count query - No order clause requred
        $count_sql = $sql;
        
        if($sort != ""){
            $sql .=  " ORDER BY " . self::Decode($sort) . " $sort_type ";
        } elseif($order != ""){
            $sql .=  " ORDER BY " . $options['order'];
        }

        if($return_data != "QUERY"){
            self::Connection();
            
            //Start : Pagination section 
            if($pagination == "YES"){
                if($total_records == 0){
                    try{
                        $innerQryName = "";
                        if(self::$protocol != 'ORACLE'){
                            $innerQryName = " AS query ";
                        }

                        $stmt = self::$connection->prepare("SELECT COUNT(*) AS count {$having_columns} FROM (SELECT 1 AS count " . $count_sql . ") {$innerQryName} ");
                        $stmt->execute();
                        $rec = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $mainData["total_records"] = $total_records = ($rec["count"]) ? $rec["count"] : 0;

                    }catch(Exception $e){
                        throw new EasyListException("Error in count query : " . $e->getMessage());
                    }
                }
                
                $total_pages = intval(ceil($total_records / $page_size));
                if($page >= $total_pages){
                    $page = $total_pages;
                }
                
                $next_page = ($page === $total_pages) ? $page : $page + 1;
                $prev_page = ($page == 1) ? 1 : $page - 1;
                $offset    = ($page - 1) * $page_size;
                
                $mainData["next_page"] = $next_page;
                $mainData["prev_page"] = $prev_page;
                $mainData["last_page"] = $total_pages;
                $mainData["total_pages"] = $total_pages;
                
                if($total_pages > 0){
                    
                    switch(self::$protocol){
                        case 'MYSQL':
                            $sql .= " LIMIT {$offset},{$page_size} ";
                            break;
                        case 'SQLSRV':
                            if($offset <= 0){
                                $sql .= " OFFSET 0 ROWS FETCH NEXT {$page_size} ROWS ONLY ";
                            } else {
                                $sql .= " OFFSET {$offset} ROWS FETCH NEXT {$page_size} ROWS ONLY ";
                            }
                            break;
                        case 'ORACLE':
                            if($offset <= 0){
                                $sql .= " FETCH NEXT {$page_size} ROWS ONLY ";
                            } else {
                                $sql .= " OFFSET {$offset} ROWS FETCH NEXT {$page_size} ROWS ONLY ";
                            }
                            break;
                        case 'POSTGRESQL':
                            $sql .= " OFFSET {$offset} LIMIT {$page_size} ";
                            break;
                        case 'SYBASE':
                            $sql .= " LIMIT {$page_size} OFFSET {$offset} ";
                            break;
                        case 'INFORMIX':
                            //:TODO
                            break;
                        default:
                            break;
                    }
                    
                }
            }
            //End : Pagination section
            
            try{
                $stmt = self::$connection->prepare($select . $sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $mainData["current_page_count"] = count($data);
            }catch(Exception $e){
                throw new EasyListException("Error in the query : " . $e->getMessage());
            }
        }
        
        //Handling return data
        switch($return_data){
            case 'HTML' :
                if(isset($options["view"]) && $options["view"] != ""){
                    ob_start();
                    require $options["view"];
                    $viewData = ob_get_clean();
                } else {
                    $viewData = "";
                }
                break;

            case 'JSON' :
            case 'OBJECT' :
                $viewData = $data;
                break;

            case 'QUERY' :
                unset($mainData["page_size"]);
                unset($mainData["page"]);
                unset($mainData["total_records"]);
                unset($mainData["return_data"]);
                unset($mainData["data"]);
                unset($mainData["is_pagination"]);

                
                $mainData["query"] = $select . $sql;
                $mainData["count_query"] = "SELECT COUNT(*) AS count  {$having_columns} FROM (SELECT 1 " . $sql . ") AS query";
                $viewData = "";
                break;
        }
    
        $mainData["data"] = $viewData;

        if($return_data == "OBJECT"){
            $mainData = (object) $mainData;
        } else {
            $mainData = json_encode($mainData,  JSON_INVALID_UTF8_IGNORE |  JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        
        self::$connection = null;
        
        return $mainData;
    }
    
    /**
     * @param array $condition
     * @return string
     * Description : Prepare condtions by including values 
     */
    public static function ConditionBuilder($condition){
        $result = "";
        
        foreach($condition AS $eachCondition){
            $subCondition = "";
            $subValues = "";
            
            $clean_condition = self::CeanQuotes($eachCondition['condition']);
            $ary_subCondition = explode("?", $clean_condition);
            
            for($i = 0; $i < count($ary_subCondition); $i++){
                if($i == 0) {continue;}
                
                if(is_array($eachCondition['value'])){
                    if(is_array($eachCondition['value'][$i-1])){
                        $subValues = "'" . implode("','", $eachCondition['value'][$i-1]) . "'";
                        $subCondition .= $subValues;
                    } else {
                        $subCondition .= "'" . $eachCondition['value'][$i-1] . "'";
                    }
                } else {
                    $subCondition .= "'" . $eachCondition['value'] . "'";
                }
                
                $subCondition .= " " . $ary_subCondition[$i];
            }
            
            $result .= $ary_subCondition[0] . $subCondition . " " . $eachCondition["operation"] . " ";
        }
        
        $result = trim($result, 'AND ');
        $result = trim($result, 'OR ');
        
        return $result;
    }
    
    /**
     * @param String $condition
     * @return String
     * Description : Remove quotes before question marks
     */
    public static function CeanQuotes($condition){
        $result = $condition;
        
        $pattern = "/'\s*\?\s*'/i";
        $result = preg_replace($pattern, '?', $result);
        $pattern = '/"\s*\?\s*"/i';
        $result = preg_replace($pattern, '?', $result);
        
        return $result;
    }
    
    /**
     * @param array $filter
     * @return string
     * Description : Prepare filter based on the conditions 
     */
    public static function ConditionBuilderForFilter($filter){
    
        $result      = "";
        $methodArray = (!empty($_POST)) ? $_POST : $_GET;
        $listFilter = new ListFilter();
        
        foreach($filter AS $eachfilter){
            $subfilter = "";
            $complextype = isset($eachfilter['type']) ? trim(strtoupper($eachfilter['type'])) : "";
            
            if($complextype == "COMPLEX"){
                foreach($eachfilter['condition'] AS $subEachFilter){
                    $subfilter .= $listFilter->filter($subEachFilter, $methodArray);
                }
            } else {
                $subfilter = $listFilter->filter($eachfilter, $methodArray);
            }
            
            $result .= $subfilter;
        }
        
        $result = trim($result, 'AND ');
        $result = trim($result, 'OR ');

        return $result;
    }
    
    /**
     * 
     * @param String $string
     * @return String
     * Description : Encode to base64
     */
    public static function Encode($string)
    {
        return base64_encode($string);
    }
    
    
    /**
     * 
     * @param String $string
     * @return String
     * Description : Decode String from base64
     */
    public static function Decode($string)
    {
        return base64_decode($string);
    }
    
    /**
     * @param array $config
     *  array(
        "url"            => "<target location of controller/action function>",
        'form_id'        => '<form id >',
        'target_div_id'  => '<div id where we want to show the output>',
        'button_id'      => '<filter button id>',
        "autolist"       => <true | false : If true will show the output first time without pressing button>,
        "column"         => array( //Provide header detail
                                array("head" => "Code", "column" => "a_code", "width" => '30%',"sort" => "a_code", "class" => "text-center"),
                                array("head" => "Town", "column" => "a_town", "width" => '30%', "class" => "","sort" => "a_town"),
                                array("head" => "Address", "column" => "a_state", "width" => '40%', "class" => "","sort" => "address")
                           ),
        "data"           => <Data retruned by the Page function>
        "return_data"    => "<HTML / JSON / OBJECT>"
        "pager"          => "TOP / BOTTOM / BOTH", //Location where we want to show the page controller 
        'page_size'      => <pagination sizes. Default is array(10,25,50,100,250) >,
                            //Action colum where we can add edit/delete buttons 
        "action"         => array("<a href='http://www.test.com/{a_country}/{name}/index'><span class='glyphicon glyphicon-pencil'></span></a>&nbsp;",
                                "&nbsp;<a href='http://www.test.com/{id}/{name}/delete'><i style='color:red;font-size:20px;' class='fa fa-trash-o'></i></a>&nbsp;",
                                '<a href="http://www.test.com/{xx}/view"><span style="font-size:20px;" class="fa fa-eye"></span></a>'
                           )
       )
     */
    public static function List($config)
    {
        $return_data = isset($config["return_data"]) ? strtoupper(trim($config["return_data"])) : "JSON";
        $page_sizes  = isset($config["page_sizes"]) ? $config["page_sizes"] : array(10,25,50,100,250);
        $formid      = isset($config["form_id"]) ? $config["form_id"] : "";
        $pager       = (isset($config["pager"]) && in_array(strtoupper(trim($config["pager"])), array('BOTH','TOP','BOTTOM'))) ? strtoupper(trim($config["pager"])) : "TOP";
        $random      = rand (10000, 99999);
        
        if($formid == ""){
            throw new EasyListException("Form_id should not be NULL");
        }
        
        if($return_data == "JSON" || $return_data == "HTML"){
            if(!array_key_exists("url", $config) || !array_key_exists("form_id", $config) || !array_key_exists("target_div_id", $config) ||
                !array_key_exists("button_id", $config) || !array_key_exists("column", $config) ||!array_key_exists("pager", $config))
            {
                  throw new EasyListException("Required parameters missing. Check these parameter : url, form_id, target_div_id, button_id, column, pager.");
            }
            
            if(array_key_exists('column', $config)){
                $config['column'] = array_map(function($obj){ $obj['sort'] = base64_encode(trim($obj['sort'])); return $obj; }, $config['column']);
            }
            
            $config = rawurlencode(str_replace('null', '""', json_encode($config)));
            
            echo '<input type="hidden" id="easylist-config" value=\''.$config.'\' />';
            
        } elseif($return_data == "HTML"){
            
            $tabledata = $config["data"]["data"];
                        
            $table      = new ListTable();
            $pagerData  = $table->pager($tabledata, $formid, $page_sizes, $random);
            $scripts    = $table->jsScripts($random, $formid);
            
            //Print pager at Top
            if($pager == "TOP" || $pager == "BOTH"){
                echo $pagerData;
            }
            
            //Print table & Script
            echo $tabledata;
            echo $scripts; 
            
            //Print pager at Bottom
            if($pager == "BOTTOM" || $pager == "BOTH"){
                echo $pagerData;
            }
            
        } elseif($return_data == "OBJECT"){
            $table      = new ListTable();
            $scripts    = $table->jsScripts($random, $formid);
            $pagerData  = $table->pager($config['data'], $formid, $page_sizes, $random);
            $tableData  = $table->table($config,$random);
            
            
            //Print pager at Top
            if($pager == "TOP" || $pager == "BOTH"){
                if($config['data']->total_records > $config['data']->page_size){
                    echo $pagerData;
                }
            }
            
            //Print table & Script
            echo $tableData;
            echo $scripts;
            
            //Print pager at Bottom
            if($pager == "BOTTOM" || $pager == "BOTH"){
                if($config['data']->total_records > $config['data']->page_size){
                    echo $pagerData;
                }
            }
        }
    }
    
    /**
     * @param array $options
     * @throws EasyListException
     *  array(
        "page"       => <return data of Page function >,
        'form_id'    => <form id >,
        'page_size'  => <pagination sizes. Default is array(10,25,50,100,250) >,
       )
     * Description : This will show pagination control in the view page 
     */
    public static function Pager($options)
    {
        $page        = isset($options["page"]) ? $options["page"] : array();
        $formid      = isset($options["form_id"]) ? $options["form_id"] : "";
        $page_sizes  = isset($options["page_sizes"]) ? $options["page_sizes"] : array(10,25,50,100,250);
        $random      = rand (10000, 99999);
        
        if($formid == ""){
            throw new EasyListException("Form_id should not be NULL");
        }
        
        if($page->total_records > $page->page_size){
            $table   = new ListTable();
            $pager   = $table->pager($page, $formid, $page_sizes, $random);
            $scripts = $table->jsScripts($random, $formid);
            
            echo $pager;
            echo $scripts;
        }
    }

    public static function sortHead($header, $column, $class, $formid){
        try{
            $random  = rand (10000, 99999);
            $column = Listing::Encode($column);
            $table   = new ListTable();
            echo $table->getTableHead($header, $column, $class, $random, $formid);
        }
        catch(Throwable $e){
            throw new EasyListException("Header exception");
        }
    }
    
}
