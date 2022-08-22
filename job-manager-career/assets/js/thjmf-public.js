var thjmf_public  = (function($, window, document) {
	'use strict';

	$(window).on('resize', function(){
		if( !$('#thjmf_apply_now_popup').hasClass('thjmf-popup-active') ){
			return;
		}

		var screen_width = $(window).width();
		var popup = $('#thjmf_apply_now_popup');
		var popup_container = popup.find('.thjmf-popup-wrapper');
		var height = popup_container.height();
		var scroll_h = (height - 106 )+'px';

		var width = '';
		var left = '';
		var top = '12px';
		var bottom = '12px';
        var admin_bar = $('#wpadminbar').length ? true : false;
		if( screen_width <= 500 ){
			width = '78%';
			left = '12%';
			top = admin_bar ? '56px' : top;
			bottom = '80px';
		}else if( screen_width <= 768 && screen_width > 500 ){
			width = '62%';
			left = '19%';
			top = admin_bar ? '56px' : top;
			bottom = '80px';
		}else if( screen_width > 768 && screen_width <= 1025){
			width = '50%';
			left = '25%';
            top = admin_bar ? '44px' : top;
			if( screen_width <= 783 && admin_bar ){
				top = '56px';
			}
		}else if( screen_width > 1025){
			width = '40%';
			left = '30%';
            top = admin_bar ? '44px' : top;
		}

		popup.find('.thjmf-popup-outer-wrapper').css('height',scroll_h);
		popup.find('.thjmf-popup-wrapper').css({
			'top'  : top, 
			'bottom'  : bottom, 
			'left' : left,
			'width': width 
		});
    });

    $("#thjmf_resume").change(function () {
        var fileExtension = ['pdf', 'doc', 'docx'];
        if( fileupload_change_event( $(this).val() ) ){
        	alert("Only these formats are allowed : "+fileExtension.join(', '));
        	$(this).val('');
        }
    });

    function fileupload_change_event( $ext ){
    	var fileExtension = ['pdf', 'doc', 'docx'];
        if ($.inArray($ext.split('.').pop().toLowerCase(), fileExtension) == -1) {
            return true;
        }
        return false;
    }
	
	function eventSavePopupForm(e, elm){
		var validation_msg = '';
		var form = $(elm).closest('form');
		var valid = validateApplyNowForm(form);
		if($.isArray(valid) && valid.length !== 0 && valid !== '' ){
			e.preventDefault();
			validation_msg = render_validation_msgs( valid );
			var form_notice = form.find('.thjmf-validation-notice');
			form_notice.html(validation_msg).focus();
		}
	}

	function render_validation_msgs( msgs ){
		var errors = '';
		$.each(msgs, function( index, el) {
			errors += '<p>'+el+'</p>';
		});
		return errors;
	}

	function validateApplyNowForm(form){
		var validation_arr = [];
		form.find('.thjmf-validation-error').removeClass('thjmf-validation-error');
		form.find('.thjmf-validation-required').each(function() {
			var field = $(this).find('input');
			var field_type = field.attr('type');
			if(field_type == null){
				field_type = field.is('select') ? 'select' : "";
			}
			var field_name = field.attr('name');
			var label = setValidationProps( field_name, field );

			switch(field_type){
				case 'text':
				default:
					if( field.val() == '' || field.val() == null ){
						validation_arr.push( label+' is a required field' );
					}else if( field_name == 'thjmf_email' && !isEmail( field.val() ) ){
						validation_arr.push( 'Invalid '+label );
					}
					break;
				case 'radio':
					if( form.find('input[name="'+field_name+'"]:checked').val() == null ){
						validation_arr.push( label+' is a required field' );
					}
					break;
				case 'file':
					if( fileupload_change_event( form.find('input[name="'+field_name+'"]').val() ) ){
						validation_arr.push( label+' is a required field' );
					}
					break;
			}
		});
		return validation_arr;
	}

	function setValidationProps( field_name, field ){
		var label = '';
		var label_elm = field.closest('.thjmf-form-field').find(' > label');
		if(label_elm.length){
			label = label_elm[0].childNodes[0].nodeValue;
			label = label !== '' ? label : str_replace('_', ' ', field_name);
			label = '<b>'+label+'</b>';
		}
		return label;
	}

	function eventApplyJob(elm){
		var screen_width = $(window).width();
		var popup = $('#thjmf_apply_now_popup');
		var popup_content = popup.find('.thjmf-popup-content');
		popup_content.find('.thjmf-validation-notice').html('');
		var popup_container = popup.find('.thjmf-popup-wrapper');
		popup.css({
			visibility: 'hidden',
			display: 'block',
		});
		var width = popup_container.width();	
		popup.css({
			visibility: '',
			display: '',
			left : '',
		});

		var width_styles = "popup_style" in thjmf_public_var ? thjmf_public_var.popup_style : false;
		var width1 = width_styles && "width1" in width_styles ? width_styles.width1 : '40%';
		var width2 = width_styles && "width2" in width_styles ? width_styles.width2 : '50%';
		var width3 = width_styles && "width3" in width_styles ? width_styles.width3 : '62%';
		var width4 = width_styles && "width4" in width_styles ? width_styles.width4 : '78%';

		var width = '';
		var left = '';
		var top = '12px';
		var bottom = '12px';
        var admin_bar = $('#wpadminbar').length ? true : false;
		if( screen_width <= 500 ){
			width = width4;
			left = '12%';
			top = admin_bar ? '56px' : top;
			bottom = '80px';
		}else if( screen_width <= 768 && screen_width > 500 ){
			width = width3;
			left = '19%';
			top = admin_bar ? '56px' : top;
			bottom = '80px';
		}else if( screen_width > 768 && screen_width <= 1025){
			width = width2;
			left = '25%';
            top = admin_bar ? '44px' : top;
			if( screen_width <= 783 && admin_bar ){
				top = '56px';
			}
		}else if( screen_width > 1025){
			width = width1;
			left = '30%';
            top = admin_bar ? '44px' : top;
		}


		popup.find('.thjmf-popup-wrapper').css({
			'top'  : top, 
			'bottom'  : bottom, 
			'left' : left,
			'width': width 
		});
		popup.addClass('thjmf-popup-active');
		var height = popup_container.height();
		var scroll_h = (height - 106 )+'px';
		popup.find('.thjmf-popup-outer-wrapper').css('height',scroll_h);
	}

	function eventClosePopup(elm){
		var popup = $('#thjmf_apply_now_popup');
		popup.removeClass('thjmf-popup-active');
		popup.find('.thjmf-validation-notice').html('');
	}

	function filterJobsEvent(elm){
		var form = $('#thjmf_job_filter_form');
		var all_blank = true;
		form.find('select').each(function(index, el) {
			if( $(this).val() != '' ){
				all_blank = false;
			}
		});
		if( all_blank){
			event.preventDefault();
			alert('No filter criteria selected');
		}
	}

	function isEmail(email) {
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	}
	
	return {
		eventApplyJob  : eventApplyJob,
		eventClosePopup : eventClosePopup,
		eventSavePopupForm : eventSavePopupForm,
		filterJobsEvent : filterJobsEvent,
   	};
}(window.jQuery, window, document));	

function thjmEventApplyJob(elm){
	thjmf_public.eventApplyJob(elm);		
}

function thjmEventClosePopup(elm){
	thjmf_public.eventClosePopup(elm);
}

function thjmEventSavePopupForm(e, elm){
	thjmf_public.eventSavePopupForm(e, elm);
}

function thjmfFilterJobsEvent(e){
	thjmf_public.filterJobsEvent(e);
}