$(function () {
    
    parseValue = function(input){
        //input = input.replace(".","");
        var re = /[^\d]*(\d+)[^\d]*/; 
        var match = re.exec(input);
        if (match && match[1]){
            return parseFloat(match[1]);
        }
        else{
            return 0;
        }
    };
    
    
    updateSum = function(){
        var sum = $("#costs").find("table tr:last td:nth-child(2)").text();
        var sumF = parseValue(sum);
        var rawValues = $("#costs").find("table tr td:nth-child(2)").each(function(e){return $(e).text()});
        var allCosts = 0;
        for(var i=0;i<rawValues.length;i++){
            allCosts += parseValue($(rawValues[i]).text());
        }
        return allCosts-sumF;
        //rawValues.each(function(e){parseValue(e);});        
    };
    
    updateNaklady = function(step, possiblePayments){
        var template = ["<tr><td class='text-left'>","</td><td>", " Kč </td></tr>"];
        var sumElem = $("#costs").find("table tr:last td:nth-child(2)");
        
        switch (step){
            case 2:
                $('input[name^="cat"]').on("click", function(){
                    var elem = $(this);
                    var index = -1;
                    switch (elem.val()){
                        case "90":
                            index = 7;
                            break;
                        case "182":
                            index = 8;
                            break;
                        case "183":
                            index = 9;
                            break;
                    }       
                    var newCost = possiblePayments[index];
                    var toCheck = newCost.desc.split(" ")[1];
                    if (elem.is(":checked")){
                        sumElem.closest("tr").before(template[0]+newCost.desc+template[1]+newCost.value+template[2]);
                    }else{
                        sumElem.closest("table").find("tr td:contains('"+toCheck+"')").closest("tr").remove();
                    }
                    var newSum = updateSum();
                    sumElem.html("<strong>"+newSum+" Kč</strong");   
                });
                break;
            case 4:
                $("input[name='choice']").on("change", function(){
                    if ($("input[name='choice']:checked").val() == 3){
                        var newCost = possiblePayments[10];
                        sumElem.closest("tr").before(template[0]+newCost.desc+template[1]+
                                        newCost.value+template[2]);
                    }else{
                        var suspiciousElem = sumElem.closest("tr").prev();
                        if (suspiciousElem.text().indexOf("Zahraniční") >= 0 && 
                            suspiciousElem.text().indexOf("společník") >= 0){
                            suspiciousElem.remove();
                        }                            
                    }
                    var newSum = updateSum();
                    sumElem.html("<strong>"+newSum+" Kč</strong");   
                });
                break;
            case 6:
                $("input[name='choice']").on("change", function(){
                    if ($("input[name='choice']:checked").val() == 3){
                        var newCost = possiblePayments[11];
                        sumElem.closest("tr").before(template[0]+newCost.desc+template[1]+
                                        newCost.value+template[2]);
                    }else{
                        var suspiciousElem = sumElem.closest("tr").prev();
                        if (suspiciousElem.text().indexOf("Zahraniční") >= 0 && 
                            suspiciousElem.text().indexOf("jednatel") >= 0){
                            suspiciousElem.remove();
                        }                            
                    }
                    var newSum = updateSum();
                    sumElem.html("<strong>"+newSum+" Kč</strong");   
                });
                $("input[name='actions']").on("change", function(){
                    if ($("input[name='actions']:checked").val() == 4){
                        var newCost = possiblePayments[12];
                        sumElem.closest("tr").before(template[0]+newCost.desc+template[1]+
                                        newCost.value+template[2]);
                    }else{
                        var suspiciousElem = sumElem.closest("tr").prev();
                        if (suspiciousElem.text().indexOf("Jiný způsob jednání") >= 0){
                            suspiciousElem.remove();
                        }                            
                    }
                    var newSum = updateSum();
                    sumElem.html("<strong>"+newSum+" Kč</strong");   
                });
                break;
            case 7:
                $("input[name='taxes[]'][value='2']").on("change", function(){
                   if ($("input[name='taxes[]'][value='2']:checked").length > 0){
                     var newCost = possiblePayments[13];
                        sumElem.closest("tr").before(template[0]+newCost.desc+template[1]+
                                        newCost.value+template[2]);
                    }else{
                        var suspiciousElem = sumElem.closest("tr").prev();
                        if (suspiciousElem.text().indexOf("Daň") >= 0){
                            suspiciousElem.remove();
                        }                            
                    }
                    var newSum = updateSum();
                    sumElem.html("<strong>"+newSum+" Kč</strong");   
                });
                break;
            case 5:
                var elem = $("#costs").find("table tr:nth-child(4) td:nth-child(2)")[0];  
                var value = inteliParseFloat($("input[name='value']").val(),false,2);
                var base = 4000;
                var sum = 0;
                var percentage = [0.02,0.012,0.006,0.003,0.002,0.001,0.0005];
                var values = [0,100000,500000,1000000,3000000,20000000,30000000,100000000];
                for (var i=1;i<percentage.length;i++){
                    if (value > (values[i]-values[i-1])){
                        sum += (values[i]-values[i-1])*percentage[i-1];
                        value -= values[i];
                    }else{
                        sum += value*percentage[i-1];
                        break;
                    }
                }
                var newVal = (sum>base) ? sum : base;
                $(elem).text((newVal*1.21).toFixed(0) +" Kč" + " ("+newVal+" + "+(newVal*0.21).toFixed(0)+" DPH)");
                var newSum = updateSum();
                sumElem.html("<strong>"+newSum+" Kč</strong");        
                return (sum>base) ? sum : base;            
                //break;
    }
    
        var newSum = updateSum();
        sumElem.html("<strong>"+newSum+" Kč</strong");        
    };
    
    
});