var header = {};
var headerColums = [];
var dateColumns = [];
var boolColumns = [];
var form = "";
var button = "";
$(document).ready(function(){
	if($('#easylist-config').length > 0){
	function getConfiguration(type=''){
		
			header = isJsonObject(decodeURIComponent($('#easylist-config').val()));
			form = header.form_id;
			button = header.button_id;
			addUpdateHiddenField('page', 1, form);
			addUpdateHiddenField('sort', '', form);
			addUpdateHiddenField('sort_type', '', form);
		
			if(header && header.autolist == true){
				getCoreData();
			}
	}
	getConfiguration();
	$(document).on('click', '#'+button , function(e){
		e.preventDefault();
		addUpdateHiddenField('page', 1, form);
		//addUpdateHiddenField('sort', '', form);
		//addUpdateHiddenField('sort_type', '', form);
		getCoreData();
	});
	function isHeaderObjectExist(objname){
		return (header.hasOwnProperty(objname)) ? true : false;
	}

	function getCoreData(){
		if(isHeaderObjectExist("column")){
			var url = header.url;
			form = header.form_id;
			$.ajax({
				type : 'POST',
				url : url,
				dataType : 'json',
				data : $('#'+form).serialize().replace(/%5B%5D/g,'[]'),
				beforeSend: function() {
					$('#'+header.target_div_id).html('<section><div class="loader"><span style="--i: 6"></span><span style="--i: 7"></span><span style="--i: 8"></span><span style="--i: 9"></span><span style="--i: 10"></span><span style="--i: 11"></span><span style="--i: 12"></span><span style="--i: 13"></span><span style="--i: 14"></span><span style="--i: 15"></span><span style="--i: 16"></span><span style="--i: 17"></span><span style="--i: 18"></span><span style="--i: 19"></span><span style="--i: 20"></span></div></section>');
				},
				success : function(response) {
					var table = "";
					if(response.return_data == 'HTML'){
						table = response.data;
					} else {
						table 	 	 = '<table class="table table-bordered  table-condensed table-hover tank-core-table">';
						table 		+= generateWidgetHeader(header.column);
						table   	+= generateWidgetTable(response);
						table  		+= "</table>";
					}
					$('#'+header.target_div_id).html(table);
					if(response.hasOwnProperty('is_pagination') && response.is_pagination == 'YES'){
						addUpdateHiddenField('page_size', response.page_size, form);
						widgetPagination(response);
					}
					applySortClass();
				},
				error : function(response) {
					// $('html, body').animate({scrollTop : 0}, 400);
					// $('form').find('#response').empty().prepend(alert_error).fadeIn();
				}
			});
		}		
	}

	function generateWidgetTable(widgetData){

		var hasAction = isHeaderObjectExist("action");
		var count = parseInt(header.column.length) +  parseInt(hasAction ? 1 : 0);
		var table	   = '';

		if(widgetData.return_data == 'JSON'){
			if(widgetData.data.length != 0){
				$.each(widgetData.data, function (i, witem) {
					var eachHtmlItems =  "";
					table +="<tr>";
					$.each(headerColums, function (keyhead, val) {
						
						if(witem.hasOwnProperty(val)){
							actual_value = (witem[val] == null) ? ''  :  witem[val];
							var key = val;
							if(dateColumns[key] != ''){
								actual_value = witem[val] ? 
									getFormattedDate(
										witem[val],
										dateColumns[key]['to_format']? dateColumns[key]['to_format']: "dd/mm/yy",
										dateColumns[key]['from_format'] ? dateColumns[key]['from_format']: "yy-mm-dd"
										) : '';
							}

							if(boolColumns[key] != ''){
								if(boolColumns[key] == 'YesNo'){
									actual_value = witem[val] == true || witem[val] == 1 ? 'Yes' : 'No';
								}
								else{
									actual_value = witem[val] ? 'True' : 'False';
								}
							}
							table +='<td class="text-left">'+actual_value+'</td>';
						}
					});
					if(hasAction){
						urlValue = header.action.join('');
							var mySubUrl = urlValue.match(/(?<=\{)(.*?)(?=\})/g);
							mySubUrl.forEach(function (urlEachItem) {

								if(witem.hasOwnProperty(urlEachItem)){
									urlValue = urlValue.replace('{'+urlEachItem+'}', witem[urlEachItem]);
								}else{
									urlValue = urlValue.replace('{'+urlEachItem+'}', 0);
								}
							});
							eachHtmlItems += urlValue;
						table += '<td style="min-width:89px;width:18%;" class="text-center">'+eachHtmlItems+'</td>';
					}
					table +="</tr>";
				});
			}else{
				table += '<tr class="text-center"><td class="warning" colspan="'+count+'"  style="text-align: center; vertical-align: middle;">No Record Found</td></tr>';
			}			
		}
			
		return table;
	}

	function generateWidgetHeader(tableheader){
		var table = '';
		headerColums = [];
		dateColumns = [];
		boolColumns = [];
		$.each(tableheader, function (i, item) {
			if( item.hasOwnProperty('head') && item.hasOwnProperty('column') ){
				table += '<th ';
				if(item.hasOwnProperty('class')){ 
					table += ' class="'+item.class+'" ';
				}
				if(item.hasOwnProperty('width')){ table += ' width="'+item.width+'" '; }
				table += '>';
				if(item.hasOwnProperty('sort') && item.sort != ""){
					table += '<a href="javascript:void(0)" class="sortClass" data-sort="'+item.sort+'" data-sort-type="asc" title="Sort">'+item.head+'</a>&nbsp<i class="fa fa-lg" aria-hidden="true"></i>';
				}else{
					table += item.head;
				}
				// check date format and boolean
				if(item.js_date_format_from != undefined && item.js_date_format_from !=''){
					dateColumns[item.column] = {from_format: item.js_date_format_from};
				}else{
					dateColumns[item.column] = '';
				}
				
				if(dateColumns[item.column] && item.js_date_format_to != undefined && item.js_date_format_to !=''){
					dateColumns[item.column]['to_format'] = item.js_date_format_to;
				}

				if(item.boolean_format != undefined && item.boolean_format !=''){
					boolColumns[item.column] = item.boolean_format;
				}else{
					boolColumns[item.column] = '';
				}
				headerColums.push(item.column);
				table += '</th>';
			}
		});
		table += (isHeaderObjectExist("action")) ? '<th class="text-center">Action</th>' : "";
		table += '';
		return table;
	}

function widgetPagination(json_data){
	if(json_data){
		displayPaginationHTML(json_data);
	}
}

function isJsonObject(json_data) {
    try {
        return JSON.parse(json_data);
    } catch (e) {
        return false;
	}
}

function displayPaginationHTML(json_data){

	var total_count = json_data.total_records;
	if(total_count > 0){
		var current_page = parseInt(json_data.page);
		var page_size =  parseInt(json_data.page_size);
		var total_pages = json_data.total_pages;
		var current_page_count = json_data.current_page_count;
		var start_page = total_count == 0 ? 0 : 1;
		var min = (current_page - 1) * page_size + start_page;
		var max = min + current_page_count - start_page;
		var next_page = json_data.next_page;
		var prev_page = json_data.prev_page;
		
		var disablePrevClass = disableNextClass = disablePrevAClass = disableNextAClass = "";
		if(current_page == 1 ){
			disablePrevClass = "ic-disable";
			disablePrevAClass = "ic-disable-a";
		}
		if(current_page == total_pages){
			disableNextClass = "ic-disable";
			disableNextAClass = "ic-disable-a";
		}

		var html = `<div class="custom-pagination">
						<a href="javascript:void(0)" class="first-page `+disablePrevAClass+`"  title="First" data-page="1">
							<span class="ic ic-skip-prev `+disablePrevClass+`"></span>
						</a>
						<a href="javascript:void(0)" class="prev-page `+disablePrevAClass+`" title="Previous" data-page="`+prev_page+`">
							<span class="ic ic-fastforward-prev `+disablePrevClass+`"></span>
						</a>
						<div class="pagedisplay">
							Records `+min+` to `+(max)+` (Total `+total_count+` Results) - Page `+current_page+` of `+total_pages+`
						</div>
						<a href="javascript:void(0)" class="next-page `+disableNextAClass+`" title="Next" data-page="`+next_page+`">
							<span class="ic ic-fastforward `+disableNextClass+`"></span>
						</a>
						<a href="javascript:void(0)" class="last-page `+disableNextAClass+`" title="Last" data-page="`+total_pages+`">
							<span class="ic ic-skip  `+disableNextClass+`"></span>
						</a>
						<select class="page-limit">
							<option value="10" `+(page_size == 10 ? "selected" : "")+`>10</option>
							<option value="25" `+(page_size == 25 ? "selected" : "")+`>25</option>
							<option value="50" `+(page_size == 50 ? "selected" : "")+`>50</option>
							<option value="100" `+(page_size == 100 ? "selected" : "")+`>100</option>
							<option value="250" `+(page_size == 250 ? "selected" : "")+`>250</option>
						</select>
					</div>`;
		if(header.pager == 'TOP'){
			$('.custom-pagination').remove();
			$('#' + header.target_div_id).prepend(html);
		}else if(header.pager == 'BOTTOM'){
			$('.custom-pagination').remove();
			$('#' + header.target_div_id).append(html);
		}
		else{
			$('.custom-pagination').remove();
			$('#' + header.target_div_id).prepend(html);
			$('#' + header.target_div_id).append(html);
		}
	}
}

// Pagination button handling 
$(document).on('click', '.first-page, .last-page, .next-page, .prev-page', function(e){
	e.preventDefault();
	var page_number = $(this).data('page');
	addUpdateHiddenField('page', page_number, form);
	getCoreData();
});

//Pagination page size handling
$(document).on('change', '.page-limit', function(e){
	e.preventDefault();
	var page_size = $(this).val();
	
	addUpdateHiddenField('page_size', page_size, form);
	addUpdateHiddenField('page', 1, form);
	
	getCoreData();
});

function addUpdateHiddenField(name, value, form){
	
	if($('#'+form).find('#'+name).length <= 0){
		$("<input>").attr({
			name: name,
			id: name,
			type: "hidden",
			value: value
		}).appendTo('#'+form);
	} else {
		$('#'+form).find('#'+name).val(value);
	}
}

$(document).on('click', '.sortClass', function(){
	// $('.center-cell').removeClass('sortClass-th');
	// $(this).parent('th').addClass('sortClass-th');
	var sort = $(this).attr('data-sort');
	var sort_type = $(this).attr('data-sort-type');

	$('#'+form).find('#page').val(1);
	
	if($('#'+form).find('#sort').val() == sort){
	  	if($('#'+form).find('#sort_type').val() == 'asc'){
	  		$('#'+form).find('#sort_type').val('desc');
	  	}
	  	else {
	  		$('#'+form).find('#sort_type').val('asc');
	  	}
	}else{
		$('#'+form).find('#sort').val(sort);
		$('#'+form).find('#sort_type').val(sort_type);
	}
	getCoreData();
});

function applySortClass(){
	$('.text-center').removeClass('sortClass-th');
	$('a[data-sort="'+$('#sort').val()+'"]').parent('th').addClass('sortClass-th');
	if($('#sort_type').val() == 'asc'){
		var imgUrl = 'sort-by-asc';
		var title = 'Ascending';
	}else{
		var imgUrl = 'sort-by-desc';
		var title = 'Descending';
	}
	var ImgSrc  = $('a[data-sort="'+$('#sort').val()+'"]').siblings('.fa');
	ImgSrc.removeClass().addClass(imgUrl);
	ImgSrc.attr('title',title);
}
}
});//end of document ready

function getFormattedDate(date_string, converting_format = "dd/mm/yy", current_format = "yy-mm-dd"){
	current_format = current_format.replace(/\//g, "-");
	date_string = date_string.replace(/\//g, "-");
	var dt_split = date_string.split('-');
	dt_arr = current_format.split('-');
	var dt_date = parseInt(dt_split[dt_arr.indexOf('dd')]);
	var dt_month = parseInt(dt_split[dt_arr.indexOf('mm')]);
	var dt_year = parseInt(dt_split[dt_arr.indexOf('yy')]);
	return converting_format.replace("yy", dt_year)
							.replace("mm", (dt_month.toString().length == 1? "0": "") + dt_month)
							.replace("dd", (dt_date.toString().length == 1? "0": "") + dt_date);
}
