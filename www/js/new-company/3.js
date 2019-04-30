window.seat = new Array();
window.tarif = new Array();
window.post = new Array();
window.phone = new Array();

$(function () {
    recalculateSeat();
    if ($("#frm-seatForm-form-have_seat-0").is(":checked")) {
        $('.nav.nav-tabs a[href="#chcisidlo"]').tab('show');
    }
    else if ($("#frm-seatForm-form-have_seat-1").is(":checked")) {
        $('.nav.nav-tabs a[href="#mamsidlo"]').tab('show');
    }
    checkSeatSelection();
    $('input[type=radio]').change(function () {
        recalculateSeat();
    });
    $('.nav.nav-tabs').on('shown.bs.tab', function (e) {
        checkSeatSelection();
    });
});

function checkSeatSelection()
{
    if ($(".nav.nav-tabs .active a").attr("href") == "#chcisidlo") {
        $("#frm-seatForm-form-have_seat-0").prop('checked', true);
        $("#frm-seatForm-form-have_seat-1").prop('checked', false);
    }
    else if ($(".nav.nav-tabs .active a").attr("href") == "#mamsidlo") {
        $("#frm-seatForm-form-have_seat-1").prop('checked', true);
        $("#frm-seatForm-form-have_seat-0").prop('checked', false);
    }
}

function recalculateSeat() {
    var seatPrice = 0;
    var otherPrice = 0;
    var monthlyPrice = 0;
    var months = 0;
    var totalCost = 0;
    var seatId = $('input[class=seat]:checked').val();
    var tarifId = $('input[class=tarif]:checked').val();
    var postId = $('input[class=post]:checked').val();
    var phoneId = $('input[class=phone]:checked').val();

    if (typeof seatId !== 'undefined') {
        var seat = window.seat[seatId];
        $("#seatField").html(seat.name);
        if (months > 0) {
            seatPrice = parseInt(seat.price[months]);
        }
    }
    ;
    if (typeof tarifId !== 'undefined') {
        var tarif = window.tarif[tarifId];
        $("#monthsField").html(tarif.months);
        months = parseInt(tarif.months);
        if (typeof seat !== 'undefined') {
            seatPrice = parseInt(seat.price[months]);
        }
    }
    ;
    if (typeof postId !== 'undefined') {
        var post = window.post[postId];
        $("#postField").html(post.name.substring(0, 33));
        otherPrice += parseInt(post.price);
    }
    ;
    if (typeof phoneId !== 'undefined') {
        var phone = window.phone[phoneId];
        $("#phoneField").html(phone.name.substring(0, 33));
        otherPrice += parseInt(phone.price);
    }
    ;
    monthlyPrice = seatPrice + otherPrice;
    totalCost = monthlyPrice * months;
    $("#priceField").html(monthlyPrice);
    $("#totalCost").html(totalCost);
}

