


$(document).ready(function ()
{
	// create a connection-dialog
	/**/
	
	$('#iHead select').addClass('ui-widget ui-state-default ui-corner-all').css('padding','5px');
	
	var ww = $(window).width(),
		wh = $(window).height();
	window.relationName = false;
	dialogbox = $('<div><iframe id="editor-frame" style="border:0px none;width:'+(ww-80)+'px;height:'+(wh-80)+'px" src="about:blank"></iframe></div>').appendTo($('body'));
	dialogbox.dialog({
		autoOpen: false,
		show: 'fade',
		hide: 'fade',
		modal: true,
		height: wh-20,
		width: ww-20,
        minWidth: '600',
		title: 'connect'
	});
	
	
	$('#objectSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + $(this).val()
	});
	
	$('#templateSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + objectName + '&template=' + $(this).val()
	});
	
	$('button').each(function() { $(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}}); })
	
	
	if (objectName)
	{
		
		var call = 'crud.php?project='+projectName+'&actTemplate=table&object=#####&action=';
		var rows = Math.floor(($(window).height()-200) / 35 );
		
		for (var e in subObjects)
		{
			var subCall = call.replace('#####', objectName);
			
			// see: http://www.jtable.org/Demo/MasterChild
			var o = {
				name: e,
				title: objectProps[e][0],
				width: '3%',
				
				sorting: false,
				edit: false,
				create: false,
				searchable: false,
				display: function (subData)
				{
					var rn = this.name;
					var $img = $('<img class="subTableHandler" title="'+rn+'" data-referenceName="'+rn+'" src="templates/table/img/list.png" />');
					$img.click(function ()
					{
						// if this sub-table is alredy opened, close it (kind of toggle effect)
						if ($(this).hasClass('subOpen'))
						{
							$(this).parents('tr').next('tr.jtable-child-row').hide();
							window.relationName = false;
							$(this).removeClass('subOpen');
							return;
						}
						
						$(this).parents('tr').find('.subTableHandler').removeClass('subOpen');
						$(this).addClass('subOpen');
						//alert(rn)
						
						// define a global array to hold selected(checked) Items (loop later)
						window.selectedSubRows = [];
						window.relationName = rn;
						window.objectId = subData.record.id;
						
						var subCallAdd = '&jtSorting=id%20ASC&referenceName='+rn+'&referenceType='+objectProps[rn][1]+'&objectId=';
						//alert(subCall + 'getConnectedReferences'	+ subCallAdd + subData.record.id)
						
						$('#main').jtable(
							'openChildTable',
							$img.closest('tr'),
							{
								name: rn,
								title: objectProps[rn][0],
								paging: true,
								pageSize: 15,
								sorting: true,
								//WordsInCell: 5,
								
								selecting: true, // Enable selecting
								multiselect: true, // Allow multiple selecting
								selectingCheckboxes: true, // Show checkboxes on first column
								selectOnRowClick: false, // disable this to only select using checkboxes
								
								//defaultSorting: 'id ASC',
								actions: {
									listAction:   subCall + 'getConnectedReferences'			+ subCallAdd + subData.record.id,
									createAction: subCall + 'createSubContent'					+ subCallAdd + subData.record.id,
									updateAction: call.replace('#####', rn) + 'updateContent&referenceName='+objectName+'&referenceType='+objectProps[rn][1]+'&referenceId=' + subData.record.id,
									deleteAction: call.replace('#####', rn) + 'removeContent'
								},
								
								// check for selected items
								rowInserted: function (event, data) {
									if (data.record.__connected__ == 1) {
										window.selectedSubRows.push(data.row);
									}
								},
								
								// when the sub-table is loaded perform some DOM-Actions
								recordsLoaded: function (event, data)
								{
									// remove toggle-classes
									window.childTable.find('.jtable-close-button').on('click', function(){
										$(this).parents('tr').prev('tr').find('.subOpen').removeClass('subOpen');
										window.relationName = false;
									});
									
									// check all checkboxes of related entries
									for(var i=0,j=window.selectedSubRows.length; i<j; ++i)
									{
										window.childTable.jtable('selectRows', window.selectedSubRows[i]);
									};
									
									// remove the select/searchfield in the header above the checkboxes
									window.childTable.find('thead tr th:first-child div').empty();
									
									// create a click-handler on all checkboxes/radios
									window.childTable
										.find('td.jtable-selecting-column input[type=checkbox]')
										.each(function()
										{
											// change the checkbox to radio-buttons if the sub-element is a parent
											if(objectProps[relationName][1] == 'p')
											{
												$(this).attr('name','xxxxx').attr('type','radio')
											};
											
											$(this).on('click', function()
											{
												// create the sub-call
												var scall = call.replace('#####', objectName) + 'updateReference&objectId='+objectId+'&referenceName='+relationName+'&referenceType='+objectProps[relationName][1]+'&referenceId=' + $(this).parents('tr').attr('data-record-key') + '&connect=' + ($(this).prop('checked')?1:0);
												
												$.get(scall, function(data)
												{
													alert(data)
												});
											})
										});
									// check for access-rights and post-process buttons
									checkEditButtons($('table[data-name="'+relationName+'"]'), relationName);
								},
								
								fields: subObjects[rn]
							},
							function (data)
							{
								
								window.childTable = data.childTable;
								
								data.childTable.jtable('load');
							}
						);
					});
					return $img;
				}
			};
			
			mainObject[e] = o;
		}
		
		
		var mainCall = call.replace('#####', objectName);
		
		// Prepare main jTable
		$('#main')
		.width( $(window).width()-20 )
		.jtable(
		{
			name: objectName,
			title: objectProps[objectName][0],
			jqueryuiTheme: true,
			paging: true,
			pageSize: rows,
			sorting: true,
			//WordsInCell: 5,
			actions: {
				listAction:   mainCall + 'getList',
				createAction: mainCall + 'createNewContent',
				updateAction: mainCall + 'updateContent',
				deleteAction: mainCall + 'removeContent'
			},
			recordsLoaded: function (event, data)
			{
				checkEditButtons($('table[data-name="'+objectName+'"]'), objectName);
				//$('table[data-name="'+objectName+'"]').hide()
			},
			fields: mainObject
		});
		
		//Load main List from Server
		$('#main').css('width',($(window).width()-30)+'px').jtable('load');
		
		$('body')
		.on(
			'click', 
			'.jtable-toolbar-item-add-record', 
			function()
			{
				// is the button inside a sub-table
				var hash = '';
				if ($(this).parent().prev('.jtable-close-button').attr('class')=='jtable-command-button jtable-close-button')
				{
					hash = '&connect_to_object='+objectName+'&connect_to_id='+objectId;
				}
				
				var on = $(this).parents('.jtable-title').next('table.jtable').data('name');
				$('#editor-frame').attr('src','backend.php?ttemplate=default&columns=-1,-1,-1&project='+projectName+'&object='+on+'#id=0'+hash);
				dialogbox.dialog('open');
			}
		);
		//
		
	}
});

// hijack the function to show the edit-dialog
$.hik.jtable.prototype._showEditForm = function (row) {
	var on = row.parents('table').attr('data-name'),
		record = row.data('record'),
		id = record.id;
	$('#editor-frame').attr('src','backend.php?ttemplate=default&columns=-1,-1,-1&project='+projectName+'&object='+on+'#id='+id);
	dialogbox.dialog('open')
};
$.hik.jtable.prototype._showAddRecordForm = function () {};
	//
	//var o = window.relationName || objectName;
	//alert(o)

function checkEditButtons(tbl, on)
{
	$.get('templates/table/checkAccess.php', {
		projectName: projectName,
		objectName: on
	},
	function(data)
	{
		// {"c":0,"r":1,"u":0,"d":0,"a":1,"s":0}
		if(data != 'OK')
		{
			//alert(data);
			var a = JSON.parse(data);
			if(a.c != 1) tbl.parent().find('.jtable-toolbar-item-add-record').hide();
			if(a.u != 1) tbl.find('.jtable-edit-command-button').hide();
			if(a.d != 1) tbl.find('.jtable-delete-command-button').hide();
		}
	})
}

function openGlobalWizard (el)
{
	$('#editor-frame').attr('src', el.value);
	dialogbox.dialog('open');
};


/**
* saves Settings and redirects to index.php
* name: logout
*/
function logout()
{
	$.get('crud.php', 
	{
		action: 'logout', 
		projectName: projectName
	})
	.always(function() {
		window.location = 'index.php?project='+projectName;
	})
};
