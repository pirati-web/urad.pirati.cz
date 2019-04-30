$(function () {
   
$("input[name='company']").keypress(function (e) {
    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
        $('button[name="search"]').click();
        return false;
    } else {
        return true;
        }
});

$("#snippet--name").on("click",".company-info-btn",function(){
                                    var elem = $(this).closest(".company-info");
                                    var ico = elem.find(".ico");
                                    $('input[name="ico"]').attr("value",ico.text());      
                                    var name = elem.find(".cname");
                                    $('input[name="company_name"]').attr("value",name.text());
                                    var seat = elem.find(".seat");
                                    $('input[name="seat"]').attr("value",seat.text());                                                                         
                                    $(".company-info").each(function(){$(this).css("background","");})
                                    elem.css("background","#ffe6a3");
                                });
                     
$("#snippet--name").on("mouseenter",".company-info",function(){
                                    var elem = $(this);//.closest(".company-info");
                                    var ico = elem.find(".ico");
                                    $('input[name="ico"]').attr("value",ico.text());      
                                    var name = elem.find(".cname");
                                    $('input[name="company_name"]').attr("value",name.text());
                                    var seat = elem.find(".seat");
                                    $('input[name="seat"]').attr("value",seat.text());                                                                         
                                    $(".company-info").each(function(){$(this).css("background","");})
                                    elem.css("background","#ffe6a3");
                                });
                            });                            