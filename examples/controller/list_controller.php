<?php

use Http\Client\Exception;
use EasyList\Listing;

/**
 *	List controller 
 */
class ListController
{

    function dynamic()
    {
        render("list/dynamic", array(
            'data' => $data //pass whatever data you want to prepare filter form 
        ), "layouts/basic");
    }
    
    
    function dynamic_table()
    {
        render("list/dynamic_table", array(
        ), "layouts/basic");
    }


    function postback()
    {
        
        $result = Listing::Page(
            array(
                "select"=>"un_id, un_locode, un_name_wo_diacritics, un_sub_division, un_latitude, un_longitude, un_fn_port, un_fn_rail, cn.name AS country"
                , "from"=>"un_locodes AS ul"
                , "joins" => "INNER JOIN country AS cn ON cn.iso = ul.un_country_code"
                , "order" => "un_locode ASC"
                , "pagination" => "YES"
                , "page_size"=>10
                ,"filters" => array(
                    array("condition" => "un_locode = ?", "form-field" => "code-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_name_wo_diacritics LIKE '%?%'", "form-field" => "company-name-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_sub_division LIKE '%?%'", "form-field" => "town-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code = ?", "form-field" => "country-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code IN (?)", "form-field" => "country-multi-filter", "operation" => "AND", "type"=>"ARRAY", "consider-empty" => "NO"),
                  )                   
                ,"return_data" => "OBJECT"
            ));
        
        render("list/postback", array(
            'result' => $result,
            'data' => $data //pass whatever data you want to prepare filter form 
        ), "layouts/basic");
        
    }
    
    
    function postback_auto()
    {
        $result = Listing::Page(
            array(
                "select"=>"un_id, un_locode, un_name_wo_diacritics, un_sub_division, un_latitude, un_longitude, un_fn_port, un_fn_rail, cn.name AS country, un_date"
                , "from"=>"un_locodes AS ul"
                , "joins" => "INNER JOIN country AS cn ON cn.iso = ul.un_country_code"
                , "order" => "un_locode ASC"
                , "pagination" => "YES"
                , "page_size"=>10
                ,"filters" => array(
                    array("condition" => "un_locode = ?", "form-field" => "code-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_name_wo_diacritics LIKE '%?%'", "form-field" => "company-name-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_sub_division LIKE '%?%'", "form-field" => "town-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code = ?", "form-field" => "country-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code IN (?)", "form-field" => "country-multi-filter", "operation" => "AND", "type"=>"ARRAY", "consider-empty" => "NO"),
                    array("type"=>"COMPLEX", "operation" => "AND", "condition" =>array(
                        array("condition" => "un_date >= ? ", "form-field" => "date_from", "operation" => "AND", "type"=>"DATE", "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", "consider-empty" => "NO"),
                        array("condition" => "un_date <= ? ", "form-field" => "date_to", "operation" => "AND", "type"=>"DATE", "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", "consider-empty" => "NO"),                        
                    )),
                )
                ,"return_data" => "OBJECT"
            ));

        render("list/postback_auto", array(
            'result' => $result,
            'data' => $data //pass whatever data you want to prepare filter form 
        ), "layouts/basic");
        
    }
    
    function commonAjax()
    {
        $result = Listing::Page(
            array(
                "select"=>"un_id, un_locode, un_name_wo_diacritics, un_sub_division, un_latitude, un_longitude, un_fn_port, un_fn_rail, cn.name AS country, un_date"
                , "from"=>"un_locodes AS ul"
                , "joins" => "INNER JOIN country AS cn ON cn.iso = ul.un_country_code"
                , "order" => "un_locode ASC"
                , "pagination" => "YES"
                , "page_size"=>10
                ,"filters" => array(
                    array("condition" => "un_locode = ?", "form-field" => "code-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_name_wo_diacritics LIKE '%?%'", "form-field" => "company-name-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_sub_division LIKE '%?%'", "form-field" => "town-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code = ?", "form-field" => "country-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code IN (?)", "form-field" => "country-multi-filter", "operation" => "AND", "type"=>"ARRAY", "consider-empty" => "NO"),
                    array("type"=>"COMPLEX", "operation" => "AND", "condition" =>array(
                        array("condition" => "un_date >= ? ", "form-field" => "date_from", "operation" => "AND", "type"=>"DATE", "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", "consider-empty" => "NO"),
                        array("condition" => "un_date <= ?", "form-field" => "date_to", "operation" => "AND", "type"=>"DATE", "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", "consider-empty" => "NO"),
                    )),
                )
                ,"return_data" => "JSON"
            ));
        
        echo $result;
    }


    function commonAjaxTable()
    {
        $result = Listing::Page(
            array(
                "select"=>"un_id, un_locode, un_name_wo_diacritics, un_sub_division, un_latitude, un_longitude, un_fn_port, un_fn_rail, cn.name AS country"
                , "from"=>"un_locodes AS ul"
                , "joins" => "INNER JOIN country AS cn ON cn.iso = ul.un_country_code"
                , "order" => "un_locode ASC"
                , "pagination" => "YES"
                , "page_size"=>10
                ,"filters" => array(
                    array("condition" => "un_locode = ?", "form-field" => "code-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_name_wo_diacritics LIKE '%?%'", "form-field" => "company-name-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_sub_division LIKE '%?%'", "form-field" => "town-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                    array("condition" => "un_country_code = ?", "form-field" => "country-filter", "operation" => "AND", "type"=>"STRING", "consider-empty" => "NO"),
                )
                ,"return_data" => "HTML"
                ,"view" => "views/list/table.html.php"
            ));
        
        echo $result;
    }
    
    
    
    
        

}
?>
