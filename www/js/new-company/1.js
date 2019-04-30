$(function () {
	var ready = false;
	$("#snippet-step1Form-new_name").on("mouseenter", "h3", function () {
		if (!ready) {
			$("[data-toggle=popover]").popover();
			ready = true;
		}
	});

	$("#snippet-step1Form-new_name").on("click", "#responsibility", function () {
		$('button[name="next"]').toggle();
		$('button[name="search"]').toggle();
		if (!$(this).prop("checked")) {
			$('input[name="responsibility"]').attr("value", 0);
			swal("Bez vašeho souhlasu bohužel se budou nadále prohledávat firmy s podobným názvem");
		} else {
			//$(this).closest("form").toggleClass("ajax");
			//$('input[name="responsibility"]').attr("value",1);                    
		}
	});
});