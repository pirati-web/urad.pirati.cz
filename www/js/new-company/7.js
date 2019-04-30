
$(function () {
	/*$("input[name='taxes[]'][value='1']").on("click", function (e) {
	 /*if (!$(this).prop("checked")){
	 swal("Každá společnost se musí do dvou týdnů od založení zaregistrovat k dani z příjmu");
	 }
	 });*/
	$("input[name='taxes[]']").click(function (e) {
		if ($(this).attr("value") != 1 && $(this).is(':checked')) {
			if (!$("input[name='taxes[]'][value='1']").is(':checked')) {
				$("input[name='taxes[]'][value='1']").prop('checked', true);
				swal("Pokud Piráti firmu registrují k jakékoli dani, musí ji zaregistrovat i k dani z příjmů.");
			}
		}
	});
	if ($("input[name='taxes[]'][value='2']").is(':checked')) {
		$("input[name='dph']").val((parseInt($("input[name='dph']").val()) + 1) % 2);
		$("textarea[name='tax_reason']").closest(".form-group").show();
	} else {
		$("textarea[name='tax_reason']").closest(".form-group").hide();
	}
	$("input[name='taxes[]'][value='2']").on("click", function () {
		$("input[name='dph']").val((parseInt($("input[name='dph']").val()) + 1) % 2);
		$("textarea[name='tax_reason']").closest(".form-group").toggle();
	});

	if ($(".form-errors").length) {
		$(".form-errors").each(function(){
			swal($(this).html());
		});
		$(".form-errors").remove();
		$("input[name='taxes[]'][value='1']").prop('checked', true);
	}
	
});