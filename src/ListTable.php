<?php
/**
 * @package EasyList
 */
namespace EasyList;

use Exception;
use PDO;
use PDOException;
use DateTime;
use EasyList2\Exceptions\EasyListException;

class ListTable
{
    public function pager($page, $formid, $page_sizes, $random){

        $start_page = $page->total_records == 0 ? 0 : 1;
        $min = ($page->page - 1) * $page->page_size + $start_page;
        $max = $min + $page->total_pages - $start_page;
        $currLimit = $page->page_size * $page->page;
        if($currLimit > $page->total_records) $currLimit = $page->total_records;
        $sizeOptions = "";
        $disablePrevClass = $disableNextClass = $disablePrevAClass = $disableNextAClass = "";
	if($page->page == 1 ){
		$disablePrevClass = "ic-disable";
		$disablePrevAClass = "ic-disable-a";
	}
	if($page->page == $page->total_pages){
		$disableNextClass = "ic-disable";
		$disableNextAClass = "ic-disable-a";
	}
        
        if($page_sizes == null){
            $page_sizes = array(10,25,50,100,250);
        }
        
        foreach($page_sizes as $eachSize){
            $selected = ($eachSize ==  $page->page_size) ? "selected" : "";
            $sizeOptions .= "<option value='{$eachSize}' {$selected}>{$eachSize}</option>";
        }
        
        $html = "<div class='custom-pagination'>
					<a href='javascript:void(0)' class='first-page {$disablePrevAClass}' title='First' data-page='1' onclick='pagination{$random}(1,this,{$page->page_size},{$page->total_records})'>
                        <span class='ic ic-skip-prev {$disablePrevClass}'></span>
    				</a>
    				<a href='javascript:void(0)' class='prev-page {$disablePrevAClass}' title='Previous' data-page='{$page->prev_page}' onclick='pagination{$random}({$page->prev_page},this,{$page->page_size},{$page->total_records})'>
                        <span class='ic ic-fastforward-prev {$disablePrevClass}'></span>
    				</a>
    				<div class='pagedisplay'>
      					Records {$min} to {$currLimit} (Total {$page->total_records} Results) - Page {$page->page} of {$page->total_pages}
    				</div>
    				<a href='javascript:void(0)' class='next-page {$disableNextAClass}' title='Next' data-page='{$page->next_page}' onclick='pagination{$random}({$page->next_page},this,{$page->page_size},{$page->total_records})'>
      					<span class='ic ic-fastforward {$disableNextClass}'></span>
    				</a>
    				<a href='javascript:void(0)' class='last-page {$disableNextAClass}' title='Last' data-page='{$page->last_page}' onclick='pagination{$random}({$page->last_page},this,{$page->page_size},{$page->total_records})'>
      					<span class='ic ic-skip {$disableNextClass}'></span>
    				</a>&nbsp;
					<select class='page-limit' onchange=paginationBySize{$random}({$page->page},this,{$page->total_records})>{$sizeOptions}</select>
  				</div>";
        
        return $html;
    }
    
    public function jsScripts($random, $formid){
        $isSortApply = $sortType = ""; 
        $pageSize = "10";
        $page = 1;
        if(isset($_GET['sort']) && $_GET['sort'] != "") $isSortApply = $_GET['sort'];
        elseif(isset($_POST['sort']) && $_POST['sort'] != "") $isSortApply = $_POST['sort'];

        if(isset($_GET['sort_type']) && $_GET['sort_type'] != "") $sortType = $_GET['sort_type'];
        elseif(isset($_POST['sort_type']) && $_POST['sort_type'] != "") $sortType = $_POST['sort_type'];

        if(isset($_GET['page_size']) && $_GET['page_size'] != "") $pageSize = $_GET['page_size'];
        elseif(isset($_POST['page_size']) && $_POST['page_size'] != "") $pageSize = $_POST['page_size'];

        if(isset($_GET['page']) && $_GET['page'] != "") $page = $_GET['page'];
        elseif(isset($_POST['page']) && $_POST['page'] != "") $page = $_POST['page'];

        $html = "<script>
                    function pagination{$random}(page, element, page_size, total_records){
                        //var form_id = element.closest('form').id;
                        form_id = '{$formid}';
                        updateHiddenAttribute{$random}('page', page, form_id);
                        updateHiddenAttribute{$random}('page_size', page_size, form_id);
                        updateHiddenAttribute{$random}('total_records', total_records, form_id);
                        updateHiddenAttribute{$random}('sort', '{$isSortApply}', form_id);
                        updateHiddenAttribute{$random}('sort_type', '{$sortType}', form_id);
                        document.getElementById(form_id).submit();
                    }
                    function paginationBySize{$random}(page, element,total_records){
                        //var form_id = element.closest('form').id;
                        form_id = '{$formid}';
                        var page_size = element.value;
                        updateHiddenAttribute{$random}('page', 1, form_id);
                        updateHiddenAttribute{$random}('page_size', page_size, form_id);
                        updateHiddenAttribute{$random}('total_records', total_records, form_id);
                        updateHiddenAttribute{$random}('sort', '{$isSortApply}', form_id);
                        updateHiddenAttribute{$random}('sort_type', '{$sortType}', form_id);
                        document.getElementById(form_id).submit();
                    }
                    function updateHiddenAttribute{$random}(name, value, form){
                        form_id = '{$formid}';
                        if(document.getElementById(form).elements[name]){
                            document.getElementById(form_id).elements[name].value = value;
                        }else{
                            //addHiddenField{$random}(name, value, form);
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', name);
                            input.setAttribute('value', value);
                            //append to form element that you want .
                            document.getElementById(form).appendChild(input);
                        }
                    }";
            $html .= "updateHiddenAttribute{$random}('sort', '{$isSortApply}', '{$formid}');";
            $html .= "updateHiddenAttribute{$random}('sort_type', '{$sortType}', '{$formid}');";
            $html .= "updateHiddenAttribute{$random}('page_size', '{$pageSize}', '{$formid}');";
            $html .= "updateHiddenAttribute{$random}('page', 1, '{$formid}');";

            $html .= "function applySort{$random}(currentelement){
                        form_id = '{$formid}';
                        var sortfield = currentelement.dataset.sort;
                        var sortType = currentelement.dataset.sort_type;

                        updateHiddenAttribute{$random}('sort', sortfield, form_id);
                        updateHiddenAttribute{$random}('sort_type', sortType, form_id);
                        updateHiddenAttribute{$random}('page_size', '{$pageSize}', form_id);
                        updateHiddenAttribute{$random}('page', '{$page}', form_id);
                        document.getElementById(form_id).submit();
                    }";
                    $html .= "</script>";
        return $html;
    }
    
    public function table($data, $random = ""){
        if(array_key_exists('column', $data)){

            $isSortApply = $sortType = "";
            if(isset($_GET['sort']) && $_GET['sort'] != "") $isSortApply = $_GET['sort'];
            elseif(isset($_POST['sort']) && $_POST['sort'] != "") $isSortApply = $_POST['sort'];

            if(isset($_GET['sort_type']) && $_GET['sort_type'] != "") $sortType = $_GET['sort_type'];
            elseif(isset($_POST['sort_type']) && $_POST['sort_type'] != "") $sortType = $_POST['sort_type'];

            $headerArr = array();
            $actionBit = 0;
            $tableHtml = '<table class="table table-bordered  table-condensed table-hover tank-core-table">'
                            .'<tbody>'
                                .'<tr>';
            foreach($data['column'] AS $dataHeader){
                $headerArr[] = $dataHeader['column'];
                $extraArr[$dataHeader['column']]['date_format'] = isset($dataHeader['date_format']) ? $dataHeader['date_format'] : '';
                $extraArr[$dataHeader['column']]['boolean_format'] = isset($dataHeader['boolean_format']) ? $dataHeader['boolean_format'] : '';
                if(array_key_exists('sort', $dataHeader)){
                    $sortValue = base64_encode(trim($dataHeader['sort']));
                    $sortTdbit = ($sortValue == $isSortApply) ? true : false;
                    $tableHtml              .= '<th class="'.(($sortTdbit) ? 'sortClass-th' : '').' '.((array_key_exists('class', $dataHeader)) ? $dataHeader['class'] : '').'" 
                                                width="'.((array_key_exists('width', $dataHeader)) ? $dataHeader['width'] : '').'" >
                                                
                                                <a  href="javascript:void(0)"
                                                    class="sortClass"
                                                    onclick="applySort'.$random.'(this)"
                                                    data-sort="'.$sortValue.'" 
                                                    data-sort_type="'.((strtolower($sortType) != "asc") ? 'asc' : 'desc').'" 
                                                    title="Sort">'.$dataHeader['head'].'</a>';
                    if($sortTdbit && strtolower($sortType) == "asc"){
                        $tableHtml                          .= '<i class="sort-by-asc" title="Ascending"></i>';
                    }else if($sortTdbit && strtolower($sortType) == "desc"){
                        $tableHtml                          .= '<i class="sort-by-desc" title="Descending"></i>';
                    }
                }else{
                        $tableHtml              .= '<th class="'.((array_key_exists('class', $dataHeader)) ? $dataHeader['class'] : '').'" width="'.((array_key_exists('width', $dataHeader)) ? $dataHeader['width'] : '').'" >'.$dataHeader['head'];
                }
                $tableHtml              .= '</th>';
           }
           if(array_key_exists('action', $data)){
                $tableHtml .= '<th class="text-center">Action</th>';
                $actionBit = 1;
           }
           $tableHtml          .= '</tr>';
           
           if(!empty($data['data']->data)){
            foreach($data['data']->data AS $dataTdItems){
                $tableHtml      .= '<tr>';
                $assoArray = (array) $dataTdItems;
                foreach($headerArr AS $eachHeaderColumn){
                    if(array_key_exists($eachHeaderColumn, $assoArray)){ 
                        if(!empty($extraArr[$eachHeaderColumn]['date_format']) && !empty($assoArray[$eachHeaderColumn])){
                            $value = date($extraArr[$eachHeaderColumn]['date_format'],strtotime($assoArray[$eachHeaderColumn]));
                        }else if(!empty($extraArr[$eachHeaderColumn]['boolean_format'])){
                            if($extraArr[$eachHeaderColumn]['boolean_format'] == 'YesNo'){
                                $value = $assoArray[$eachHeaderColumn] ? 'Yes' : 'No';
                            }else{
                                $value = $assoArray[$eachHeaderColumn] ? 'True' : 'False';
                            }
                        }else{
                            $value = htmlentities($assoArray[$eachHeaderColumn]);
                        }
                        $tableHtml       .= '<td class="text-left">'.$value.'</td>';
                    }
                    else{ $tableHtml       .= '<td class="text-left"></td>'; }
                }
                if(array_key_exists('action', $data)){
                    $tableHtml         .= '<td style="min-width:89px;" class="text-center">';
                    $actionItemStr = implode("", $data['action']);
                        preg_match_all('/(?<=\{)(.*?)(?=\})/', $actionItemStr, $matches);
                        foreach($matches[0] AS $eachMatch){
                            if(array_key_exists($eachMatch, $assoArray)){
                                $actionItemStr    = str_replace('{'.$eachMatch.'}', $assoArray[$eachMatch], $actionItemStr); 
                            }else{
                                $actionItemStr    = str_replace('{'.$eachMatch.'}', 0, $actionItemStr); 
                            }
                        }
                        $tableHtml         .= $actionItemStr;
                    $tableHtml         .= '</td>';
                }
                $tableHtml      .= '</tr>';
            }
            
           }else{
            $tableHtml          .= '<tr><td class="warning" colspan="'.(count($headerArr) + $actionBit).'"  style="text-align: center; vertical-align: middle;">No Record Found</td></tr>';
           }
           $tableHtml       .= '</tbody>'
                        .'</table>';

            return $tableHtml;
        }
        //$header = $data['column'];
        
        
    }
}
