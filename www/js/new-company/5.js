 $(function () {
        //$("input[name='value']").trigger("keyup");
        inteliParseFloat = function(stringVal, stringOut, precision){
            var splitVals = stringVal.split(/\.|,/)
            if (splitVals.lenght > 2){
                return NaN;
            }
            if (splitVals.lenght === 1){
                return (stringOut) ? stringVal : parseInt(stringVal); 
            }
            var partition_part = splitVals[1];
            if (!partition_part){
                return (stringOut) ? stringVal : parseInt(stringVal); 
            }else{
                if (partition_part.length  < precision){
                    return (stringOut) ? stringVal : parseFloat(stringVal); 
                }else{
                    var returnVal = parseFloat(stringVal).toFixed(precision);
                    return (stringOut) ? returnVal+"" : parseFloat(returnVal); 
                }                
            }           
        };
        
        checkErrors = function(usersCount){
            // kontrola doporučení minimálního kapitálu
            var value = $("input[name='value']").val();
            if (parseInt(value) < 1000){
                $("#capital-warn").show();
            }else{
                $("#capital-warn").hide();
            }
            
            $("#errorCheck").val(0);
            
            var intShares = true;
            var sumShares = 0;
            var sumMoney = 0;
            for(i=0;i<parseInt(usersCount);i++){
                var valOfSlider = inteliParseFloat($('#share'+i).val(),false,2);
                $('#share'+i).val(inteliParseFloat($('#share'+i).val(),true,2));
                sumShares += valOfSlider;          
                sumMoney += value*valOfSlider/100;
                intShares = intShares && (parseInt(value*valOfSlider/100) == value*valOfSlider/100);
            }
            if (sumShares != 100){
                $("#sum-error").show();            
                $("#errorCheck").val(1);
            }else{
                $("#sum-error").hide();                
            }
            
            // kontrola celočíselných podílů
            if (!intShares){
                $("#share-money-error").show();
                $("#errorCheck").val(1);
            }else{
                $("#share-money-error").hide();
            }
            
            // kontrola souctu podilu
            if (sumMoney != value){
                $("#sum-money-error").show();            
                $("#errorCheck").val(1);
            }else{
                $("#sum-money-error").hide();
            }
                
        }
        
        renderGraph = function(usersCount, stringFractions, persons){
            // render default chart
            data = []
            $("input[name='messFractions']").on("change", function(){
                var is_checked = $(this).is(":checked");
                $("[id^='money']").prop("readonly",!is_checked);
                if (!is_checked){
                    $("#sum-money-error").hide();            
                    $("input[name='value']").trigger("keyup");
                }
            });
            $("[id^='money']").on("keyup",function(){
                var override = $("input[name='messFractions']").is(":checked");
                if (override){
                    var value = parseFloat($("input[name='value']").val());
                    var sum = 0;
                    for(var i=0;i<parseInt(usersCount);i++){
                        var valOfSlider = inteliParseFloat($('#money'+i).val(),false,2);
                        $('#money'+i).val(inteliParseFloat($('#money'+i).val(),true,2));
                        sum += valOfSlider;                                 
                    }
                    checkErrors(usersCount);
                }
            });
            $("input[name='value']").keyup(function(){
                var value = $("input[name='value']").val();
                //updateNaklady(5);
                //$("input[name='value']").val(inteliParseFloat(value, true, 0));
                checkErrors(usersCount);
                var sum = 0;
                for(i=0;i<=parseInt(usersCount);i++){
                    var valOfSlider = parseFloat($('#share'+i).val());
                    sum += valOfSlider;            
                }
                var override = $("input[name='messFractions']").is(":checked");
                if (!override){
                    for(i=0;i<=parseInt(usersCount);i++){
                        var sVal = parseInt($('#share'+i).val());
                        $("#money"+i).val(parseFloat($("#share"+i).val())/100*value);
                    }
                }else{
                    $("[id^='money']").trigger("keyup");
                }
            });
            $(".share-input").on("input",function(){
                var override = $("input[name='messFractions']").is(":checked");
                var value = $("input[name='value']").val();
                var sum = 0;
                var values = Array(usersCount);
                for(i=0;i<parseInt(usersCount);i++){
                    var valOfSlider = inteliParseFloat($('#share'+i).val(),false,2);
                    $('#share'+i).val(inteliParseFloat($('#share'+i).val(),true,2));
                    sum += valOfSlider;         
                    values[i] = valOfSlider;
                }
                checkErrors(usersCount);                
                for (i=0;i<usersCount;i++){
                    var sVal = inteliParseFloat($('#share'+i).val(),false,2);
                    if (!override){
                        $("#money"+i).val(inteliParseFloat(`${sVal/sum*value}`, true));
                    }
                    var name= (persons[i]) ? persons[i] : "";
                    data[i] = [name, sVal];  
                }
                //var valuesS = $("#fractions").val();
                //var value = $("input[name='value']").val();
                values = values.map(function(e){return e/sum;});
                $("#fractions").val(values.join(","));
                RenderPieChart('container', data);
            });
            $("#fractions").val(stringFractions);            
            var fracs = stringFractions.split(",");
            for (i=0;i<usersCount;i++){
                var name= persons[i] ? persons[i] : "";
                data[i] = [name, 100*parseFloat(fracs[i])];                    
            }
            RenderPieChart('container', data);
            function RenderPieChart(elementId, dataList) {
                new Highcharts.Chart({
                    chart: {
                        renderTo: elementId,                        
                        plotBackgroundColor: "#ededed",
                        backgroundColor: "#ededed",
                        /*plotBorderWidth: null,*/
                        plotShadow: false
                    }, title: {
                        text: 'Podíly ve firmě'
                    },
                    tooltip: {
                        formatter: function () {
                            return '<b>' + this.point.name + '</b>: ' + this.percentage.toFixed(2) + ' %';
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                color: '#000000',
                                connectorColor: '#000000',
                                formatter: function () {
                                    return '<b>' + this.point.name + '</b>: ' + this.percentage.toFixed(2) + ' %';
                                }
                            }
                        }
                    },
                    series: [{
                            type: 'pie',
                            name: 'Browser share',
                            data: dataList
                        }]
                });
            }
            ;
        };
    });