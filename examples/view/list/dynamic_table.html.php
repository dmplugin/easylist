<h2>Dynamic HTML List</h2>


<?php 
EasyList\Listing::list(
    array(
        "url"            => HOME . "/list/common_ajax_table",
        'form_id'        => 'dynalist',
        'target_div_id'  => 'div-list-renders',
        'button_id'      => 'data-submit',
        "autolist"       => true,
        "column"         => array(
            array("head" => "Id", "column" => "un_id", "width" => '10%',"sort" => "un_id", "class" => ""),
            array("head" => "UN Locode", "column" => "un_locode", "width" => '10%',"sort" => "un_locode", "class" => ""),
            array("head" => "City", "column" => "un_name_wo_diacritics", "width" => '20%', "class" => "","sort" => "un_name_wo_diacritics"),
            array("head" => "Country", "column" => "country", "width" => '10%',"sort" => "country", "class" => ""),
            array("head" => "Division", "column" => "un_sub_division", "width" => '10%',"sort" => "un_sub_division", "class" => ""),
            array("head" => "Latitude", "column" => "un_latitude", "width" => '10%',"sort" => "un_latitude", "class" => ""),
            array("head" => "Longitude", "column" => "un_longitude", "width" => '10%',"sort" => "un_longitude", "class" => ""),
            array("head" => "Port", "column" => "un_fn_port", "width" => '10%',"sort" => "un_fn_port", "class" => ""),
            array("head" => "Rail", "column" => "un_fn_rail", "width" => '10%',"sort" => "un_fn_rail", "class" => ""),
        ),
        "pager"          => "BOTH",
        "return_data"    => "HTML",
        "action"         => array("<a href='http://www.test.com/{un_id}/{un_locode}/index'><span class='glyphicon glyphicon-pencil'></span></a>&nbsp;",
                                "&nbsp;<a href='http://www.test.com/{id}/{name}/delete'><i style='color:red;font-size:20px;' class='fa fa-trash-o'></i></a>&nbsp;",
                                '<a href="http://www.test.com/{xx}/view"><span style="font-size:20px;" class="fa fa-eye"></span></a>'
                           )
    )
); 
?>

<form id="dynalist" name="dynalist" action="POST">
	<button type="submit" class="btn btn-success" title="Filter" id="data-submit" ><span class="glyphicon glyphicon-filter"></span> Filter</button>
</form>
<div id="div-list-renders"></div>
