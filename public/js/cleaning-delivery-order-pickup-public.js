(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	/**
	 * Update the Delivery date option
	 */
	$(document).on('change', 'select[name="pickup_date"]', function() {
		var date_selected = jQuery('option:selected', this).val();
		var turnaround = jQuery('input[name="max_turnaround"]').val();

		$.ajax({
			type: 'POST',
			url: frontend_ajax_object.ajaxurl,
			data: {
				action: "cdop_updated_delivery_date_options",
				date_selected: date_selected,
				turnaround: turnaround,
			},
			beforeSend: function() {
				$('body').append("<div class='loader'><div class='loader-wrapper'><div class='bubble1'></div><div class='bubble2'></div></div></div>");
			},
			success: function (response) {
				if(response) {
					response = JSON.parse(response);
					$('select[name="pickup_time"]').html(response['time_slots']);
					$('select[name="pickup_time"]').prop("selectedIndex", 0);
					$('select[name="delivery_date"]').html(response['delivery_dates']);
					$('select[name="delivery_date"]').prop("selectedIndex", 0);
					$('select[name="delivery_time"]').html(response['delivery_time_slots']);
					$('select[name="delivery_time"]').prop("selectedIndex", 0);
					
				}
			},
			complete: function(){
				$('.loader').remove();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError);
			}
		});
		
	});

	/**
	 * Update the Delivery time slots
	 */
	$(document).on('change', 'select[name="delivery_date"]', function() {
		var date_selected = jQuery('option:selected', this).val();

		$.ajax({
			type: 'POST',
			url: frontend_ajax_object.ajaxurl,
			data: {
				action: "cdop_updated_delivery_timeslots_options",
				date_selected: date_selected,
			},
			beforeSend: function() {
				$('body').append("<div class='loader'><div class='loader-wrapper'><div class='bubble1'></div><div class='bubble2'></div></div></div>");
			},
			success: function (response) {
				if(response) {
					$('select[name="delivery_time"]').html(response);
					$('select[name="delivery_time"]').prop("selectedIndex", 0);
					
				}
			},
			complete: function(){
				$('.loader').remove();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError);
			}
		});
		
	});

	/**
	 * Display other field on changing radio butto selection
	 */
	$(document).on('change', 'input:radio[name="cd_radio_choice"]', function() {
		var selected_opt = $('input[name="cd_radio_choice"]:checked').val();
		if(selected_opt == "Other") {
			$('#cd_radio_choice_other_field').css('display', 'block');
		} else {
			$('#cd_radio_choice_other_field').css('display', 'none');
		}
	});
	

})( jQuery );
