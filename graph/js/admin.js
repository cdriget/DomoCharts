/******************************************************************************/
/*** File    : admin.js                                                     ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Feb - Mar 2014 : Initial release                             ***/
/***         : September 2015 : Update jQuery & jQuery-ui                   ***/
/*** Note    : Administration scripts                                       ***/
/******************************************************************************/

$(document).ready(function() {

	//*** Return a helper with preserved width of cells
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	//*** Sort
	$("table.device tbody").sortable({
		cancel: 'td.content',
		containment: 'parent',
		cursor: 'move',
		helper: fixHelper,
		//placeholder: 'ui-state-highlight',
		tolerance: 'pointer',
		update: function(event, ui) {
			// Retrieve data to be sent
			var order = $(this).sortable('serialize');
			// Ajax call
			$.post('admin-device-sort.ajax.php', order, function(data) {
				if (data.substring(0, 2) != 'OK')
					alert(data);
			});
		}
	});

	//*** Check-box
	/*$("table.device td.jqueryui input:checkbox").button().bind('click', function(event, ui) {
		var typeid = $(this).attr('id').split('_')[1];
		var deviceid = $(this).attr('id').split('_')[2];
		var visible = $(this).is(':checked')?1:0;
		$(this).blur();
		$.get('admin-device-visible.ajax.php?type='+typeid+'&device='+deviceid+'&visible='+visible, function(data) {
			if (data.substring(0, 2) == 'OK') {
				alert('OK');
				return true;
			}
			else {
				alert(data);
				return false;
			}
		});
		return true;
	});*/

	//*** Check-box
	$('.tzcheckbox input[type=checkbox]').tzCheckbox({
		labels:['Enable','Disable']
	});

	//*** Color Picker
	$(".colorpicker").jPicker({
		window: {
			effects: {
				speed: {
					show: 'fast'
				}
			},
			position: {
				x: 'screenCenter',
				y: 'bottom'
			},
			expandable: true,
			updateInputColor: false
		},
		images: {
			clientPath: 'img/jpicker/'
		}},
		function(color, context) {
			var all = color.val('all');
			var typeid = $(this).attr('id').split('_')[1];
			var deviceid = $(this).attr('id').split('_')[2];
			$.get('admin-device-color.ajax.php?type='+typeid+'&device='+deviceid+'&color='+(all && '' + all.hex || ''), function(data) {
				if (data.substring(0, 2) != 'OK')
					alert(data);
			});
		}
	);

});
