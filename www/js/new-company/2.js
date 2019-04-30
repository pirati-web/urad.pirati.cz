$(function () {
	$('input[name^="cat"]').on("click", function () {
		elem = $(this);
		if (elem.is(":checked")) {
			var whole_thing = elem.closest("li");
			clone = whole_thing.clone(false).appendTo("#selected-items");
			input = $(clone).find("input")
			input.on("click", function(){				
				input_id = $(this).attr("id")
				$("#all-fields").find("#" + input_id).attr("checked", false);
				var wt = $(this).closest("li");
				$(wt).detach()
			})
		} else {
			var whole_thing = $("#selected-items").find("#" + elem.attr("id")).closest("li");
			whole_thing.detach();
			$("#all-fields").find("#" + elem.attr("id")).attr("checked", false);			       
		}
		$('[data-toggle="popover"]').popover({
			container: 'body'
		});         
	});
	var checkAndShow = function () {
		var checkedG = $('.group-chkb:checked').map(function () {
			return $(this).val()
		});
		var uncheckedG = $('.group-chkb:not(:checked)').map(function () {
			return $(this).val()
		});

		var checkedT = $('.type-chkb:checked').map(function () {
			return $(this).val()
		});
		var uncheckedT = $('.type-chkb:not(:checked)').map(function () {
			return $(this).val()
		});
		$("#all-fields").children().hide();
		for (i = 0; i < checkedT.length; i++) {
			$("#all-fields").find(".t" + checkedT[i]).each(function () {
				$(this).parent().show()
			});
		}
		for (i = 0; checkedG.length != 0 && i < uncheckedG.length; i++) {
			$("#all-fields").find(".g" + uncheckedG[i] + ":visible").each(function () {
				$(this).parent().hide()
			});
		}
		for (i = 0; i < checkedG.length; i++) {
			$("#all-fields").find(".g" + checkedG[i]).each(function () {
				$(this).parent().show()
			});
		}
		if (checkedT.length == 0) {
			for (i = 0; i < checkedG.length; i++) {
				$("#all-fields").find(".g" + checkedG[i] + ":visible").each(function () {
					$(this).parent().show()
				});
			}
		}


	};
	$('.group-chkb').on("click", function () {
		checkAndShow();
	});
	$('.type-chkb').on("click", function () {
		checkAndShow();
	});
	$(".fields-item").hide();
	var checked = $('input[name^="cat"]:checked');
	checked.trigger("click");
	checked.trigger("click");
	$('#selected-items li').each(function () {
		$(this).show()
	})
	$("#filter").keyup(function () {

		// Retrieve the input field text and reset the count to zero
		var filter = $(this).val(), count = 0;
		if ($(this).val().length > 0) {
			// Loop through the list
			$(".fields-item").not("selected-items").find("label").each(function () {

				co_to_je = $(this);
				// If the list item does not contain the text phrase fade it out
				if ($(this).text().search(new RegExp(filter, "i")) < 0) {
					$(this).parent().parent().hide();
					// Show the list item if the phrase matches and increase the count by 1
				} else {
					$(this).parent().parent().show();
					$(this).show();
					count++;
				}
			});
			var numberItems = count;
			$("#filter-count").text("Number of Comments = " + count);
		} else {
			$("#all-fields").find(".fields-item").hide();
		}
		$('#selected-items li').each(function () {
			$(this).show()
		});
	});
	$('.type-chkb[value="2"]').prop('checked', true);
	checkAndShow();

	$("#singlebutton").click(function (e) {
		var data = $('#frm-step2Form-form').serializeArray();
		var displayPopup = false;
		for (i in data) {
			if (data[i].name.indexOf("cat") != -1) {
				var v = data[i].value;
				if (v == 2 || v == 9 || v == 90 || v == 182 || v == 183) {
					displayPopup = true;
				} else {
					displayPopup = false;
					break;
				}
			}
		}
		if (displayPopup) {
			e.preventDefault();
			$('#confirm-cat').modal('show');
			return false;
		}
		return true;
	});
	
	$("#confirm-cat-send").click(function(){
		$('#frm-step2Form-form').append($("<input>").attr("type", "hidden").attr("name", "next").val("next"));
		$('#frm-step2Form-form').submit();
	});
});