<h2>Postback List</h2>

<div class="form-horizontal">
	<div class="panel panel-default">
 		<div class="panel-body">
  			<form method="POST" action="" id="postbackform">
      			<fieldset>
				<!--- FILTERS -->
      			</fieldset>
    		</form>        
  		</div>
	</div>
</div>

<?php if($result) { 
    EasyList\Listing::Pager(array("page" => $result, "form_id" => "postbackform", "page_sizes" => array(10,25,50,100,250)));
?>

<div style="width: 100%;overflow-x: auto;">
	<table class="table table-condensed table-bordered table-hover address-table">
	  <thead>
	    <tr>
	      <th class="td-with-border center-cell">ID</th>
	      <th class="td-with-border center-cell">UN Locode</th>
	      <th class="td-with-border center-cell">City</th>
	      <th class="td-with-border center-cell">Country</th>
	      <th class="td-with-border center-cell">Division</th>
	      <th class="td-with-border center-cell">Latitude</th>
	      <th class="td-with-border center-cell">Longitude</th>
	      <th class="td-with-border center-cell">Port</th>
	      <th class="td-with-border center-cell">Rail</th>
	      <th class="td-with-border center-cell">Actions</th>
	    </tr>
	  </thead>
	  <tbody>
	    <?php foreach($result->data as $eachList) :
	           $eachList = (object) $eachList;
	    ?>
	      <tr class="success">
	      	<td width="6%">
	        	<?php echo $eachList->un_id; ?>
	        </td>
	        <td width="6%">
	        	<?php echo strtoupper($eachList->un_locode); ?>
	        </td>
	        <td width="10%" class="split-word">
	        	<?php echo $eachList->un_name_wo_diacritics; ?>
	        </td>
	        <td width="10%">
	        	<?php echo $eachList->country; ?>
	        </td>
	        <td width="10%">
	        	<?php echo $eachList->un_sub_division; ?>
	        </td>
	        <td width="10%">
	        	<?php echo $eachList->un_latitude; ?>
	        </td>
	        <td width="7%">
	        	<?php echo $eachList->un_longitude; ?>
	        </td>
	        <td width="5%">
	        	<?php echo $eachList->un_fn_port; ?>
	        </td>
	        <td width="5%">
	        	<?php echo $eachList->un_fn_rail; ?>
	        </td>
	        <td width="7%" class="center-cell">
	         <a href="#" title="Edit data" class="edit-icon"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;
	        </td>
	      </tr>
	    <?php endforeach; ?>
	  </tbody>
	</table>
</div>

<?php 
EasyList\Listing::Pager(array("page" => $result, "form_id" => "postbackform", "page_size" => array(10,25,50,100,250)));
} ?>
