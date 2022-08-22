var thjmf_settings  = (function($, window, document) {
	'use strict';

	var VALIDATOR_JOB_DETAIL_FIELDS_HTML  = '<tr><td class="thjmf-cell-nolabel">';
	VALIDATOR_JOB_DETAIL_FIELDS_HTML += '<input type="text" name="i_job_def_feature[]" value="" style="width:160px;height:30px;margin:0px 19px 0 0;" placeholder="Feature" autocomplete="'+thjmf_var['autocomplete']+'">';
	VALIDATOR_JOB_DETAIL_FIELDS_HTML += '<span class="dashicons dashicons-trash thjmf-dashicon-delete" onclick="thjmRemoveCurrentDataRow(this)"></span>';
	VALIDATOR_JOB_DETAIL_FIELDS_HTML += '</td></tr>';

   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - SATRT -----	
	*------------------------------------*/
	$(function() {
		setup_onclick_functions();
	});
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - END -------
	*------------------------------------*/
	function setup_onclick_functions(){
		setup_datepicker('thjmf-datepicker-field');
	}

	function setup_datepicker(elm){
		$('.'+elm).datepicker({
			dateFormat: "dd-mm-yy",
			minDate: 0,

		});
	}

	function toggle_switch_event_listener(elm){
		var element = $(elm);
		var checked = element.prop("checked") ? true : false;
		var wrapper = element.closest('.thjmf-switch-wrapper');
		wrapper = wrapper.length ? wrapper.find('.thjmf-switch-hidden') : element.siblings('input[type="hidden"]');
		wrapper.val(checked); 

		if( element.attr('name') == 'i_enable_apply_form'){
	        //Show/Hide apply form message based on Switch Toggle
	        var toggle_table = element.closest('.thjmf-settings-tab-form-table');
	        if( checked ){
	            toggle_table.addClass('thjmf-inactive');
	            toggle_table.find('.thjmf-toggle-row input').attr('readonly', true);
	        }else{
	        	toggle_table.removeClass('thjmf-inactive');
	            toggle_table.find('.thjmf-toggle-row input').removeAttr('readonly');
	        }
	    }
	}

	function addFeatureJobDetail(elm){
		$(VALIDATOR_JOB_DETAIL_FIELDS_HTML).insertBefore( $(elm).closest('tr') );
	}

	function removeCurrentDataRow(elm){
		$(elm).closest('tr').remove();
	}

	function copyShortcodeEvent(elm){
		var input = $(elm).closest('.thjmf-shortcode-info').find('input.thjmf-shortcode-text');
		input.select();
		document.execCommand('copy');
		alert("Shortcode copied");
		input.blur(); 
	}
	
	return {
		toggle_switch_event_listener : toggle_switch_event_listener,
		addFeatureJobDetail  : addFeatureJobDetail,
		removeCurrentDataRow  :  removeCurrentDataRow,
		copyShortcodeEvent : copyShortcodeEvent, 
   	};
}(window.jQuery, window, document));	

function thjmSwitchCbChangeListener(elm){
	thjmf_settings.toggle_switch_event_listener(elm);
}

function thjmAddFeatureJobDetail(elm){
	thjmf_settings.addFeatureJobDetail(elm);
}

function thjmRemoveCurrentDataRow(elm){
	thjmf_settings.removeCurrentDataRow(elm);
}

function thjmfCopyShortcodeEvent(elm){
	thjmf_settings.copyShortcodeEvent(elm);
}