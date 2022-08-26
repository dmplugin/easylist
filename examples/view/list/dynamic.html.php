<h2>Dynamic JS List</h2>

<div class="form-horizontal">
	<div class="panel panel-default">
 		<div class="panel-body">
  			<form method="post" action="/" id="dynalist">
      			<fieldset>
				<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-5">
				    		<button type="submit" class="btn btn-success" title="Filter" id="data-submit" ><span class="glyphicon glyphicon-filter"></span> Filter</button>
				    	</div>
				</div>
      			</fieldset>
    		</form>        
  		</div>
	</div>
</div>

<?php 
EasyList\Listing::list(
    array(
        "url"            => HOME ."list/common_ajax",
        'form_id'        => 'dynalist',
        'target_div_id'  => 'div-list-renders',
        'button_id'      => 'data-submit',
        "autolist"       => true,
        "column"         => array(
            array("head" => "Id", "column" => "un_id", "width" => '10%',"sort" => "un_id", "class" => ""),
            array("head" => "UN Locode", "column" => "un_locode", "width" => '10%',"sort" => "un_locode", "class" => ""),
            array("head" => "City", "column" => "un_name_wo_diacritics", "width" => '20%', "class" => "","sort" => "un_name_wo_diacritics"),
            array("head" => "Country", "column" => "country", "width" => '10%',"sort" => "cn.name", "class" => ""),
            array("head" => "Division", "column" => "un_sub_division", "width" => '10%',"sort" => "un_sub_division", "class" => ""),
            array("head" => "Latitude", "column" => "un_latitude", "width" => '10%',"sort" => "un_latitude", "class" => ""),
            array("head" => "Longitude", "column" => "un_longitude", "width" => '10%',"sort" => "un_longitude", "class" => ""),
            array("head" => "Port", "column" => "un_fn_port", "width" => '10%',"sort" => "un_fn_port", "class" => "", "boolean_format"=>"YesNo"),
            array("head" => "Rail", "column" => "un_fn_rail", "width" => '10%',"sort" => "un_fn_rail", "class" => "", "boolean_format"=>"YesNo"),
            array("head" => "Date", "column" => "un_date", "width" => '10%',"sort" => "un_date", "class" => "", "js_date_format_from" => "yy-mm-dd", "js_date_format_to" => "dd/mm/yy"),
        ),
        "pager"          => "BOTH",
        "return_data"    => "JSON",
        "action"         => array("<a href='http://www.test.com/{un_id}/{un_locode}/index'><span class='glyphicon glyphicon-pencil'></span></a>&nbsp;",
                                "&nbsp;<a href='http://www.test.com/{un_id}/{un_locode}/delete'><i style='color:red;font-size:20px;' class='fa fa-trash-o'></i></a>&nbsp;",
                                '<a href="http://www.test.com/{un_id}/view"><span style="font-size:20px;" class="fa fa-eye"></span></a>'
                           )
    )
); 
?>

<div id="div-list-renders"></div>
