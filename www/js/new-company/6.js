/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(function () {
    var toReplace = $('button[name="another"]').parent().html();
    toReplace = ""+toReplace.replace(/společníka/g,"jednatele");
    $('button[name="another"]').parent().html(toReplace);
    $("#snippet--name").on("click",".company-info-btn",
        function(){
            var elem = $(this).closest(".company-info");
            var ico = elem.find(".ico");
            $('input[name="ico"]').attr("value",ico.text());      
            var name = elem.find(".cname");
            $('input[name="company_name"]').attr("value",name.text());
            var seat = elem.find(".seat");
            $('input[name="seat"]').attr("value",seat.text());        
            $(".company-info").each(function(){$(this).css("background","");})
            elem.css("background","#ffe6a3");
        }
    );
    
    
       
$("input[name='company']").keypress(function (e) {
    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
        $('button[name="search"]').click();
        return false;
    } else {
        return true;
        }
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
    
    
    // hide pouze pokud uzivatel nic nevyplnil  
    //$('input[name^="actingPersons"]').trigger("click");
    /*if (!$('input[name^="actions"]:checked')){
        $('input[name^="actions"]').each(function(){
                        $(this).closest(".radio").hide();
                    });
    }*/
    
    updatePossibleValues  = function(){
            var checked = $('input[name^="actingPersons"]:checked').length;
            switch(checked){
                case 1:
                    $('input[name^="actions"]').each(function(){
                        if ($(this).val() != "1"){
                            $(this).closest(".radio").hide();
                        }else{
                            $(this).closest(".radio").show();
                            $(this).prop("checked",true);
                        }
                    });
                    break;
                case 0:
                    $('input[name^="actions"]').each(function(){
                        $(this).closest(".radio").hide();
                    });
                    break;
                default:
                    $('input[name^="actions"]').each(function(){
                        if ($(this).val() == "1"){
                            $(this).closest(".radio").hide();
                            $(this).prop("checked",false);
                        }else{
                            $(this).closest(".radio").show();
                        }
                    });
                    break;
            }    
        };
                                
    $('input[name^="actingPersons"]').on("click",updatePossibleValues);                                
    updatePossibleValues();
});