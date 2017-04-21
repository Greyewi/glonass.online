jQuery(document).ready(function()
{
    new WOW({ mobile: false }).init();

    $(function(){

      $('.btn_start').on('click', function(e){
        $('html,body').stop().animate({ scrollTop: $('#start').offset().top }, 1000);
        e.preventDefault();
      });

   });

   var islides = jQuery('.mn');
   islides.click(function()
   {
      var t = jQuery(this);
      islides.removeClass('active');
      t.addClass('active');
      $('.tabs__content').removeClass('active');
      var pp = t.attr('st');
      $( ".tabs__content[st='" + pp + "']" ).addClass('active');
   });

   $( "#slider-range-min" ).slider({
        range: "min",
        value: 5000,
        min: 4000,
        max: 10000,
        step: 100,
        slide: function( event, ui ) {
            $( "#amount" ).val(ui.value);
            if(ui.value <= 5000){
                user_reward = ui.value - 4000;
            } else if(ui.value > 5000){
                user_reward = ((ui.value - 5000) / 2) + 1000;
            }
            console.log(user_reward);
            $("#reward1").html(user_reward);
        }
    });
   $( "#amount" ).val($( "#slider-range-min" ).slider( "value" ) );

   $( "#slider-range-min2" ).slider({
       range: "min",
       value: 5000,
       min: 4000,
       max: 10000,
       step: 100,
       slide: function( event, ui ) {
           $( "#amount2" ).val(ui.value);
           if(ui.value <= 5000){
               user_reward = ui.value - 4000;
           } else if(ui.value > 5000){
               user_reward = ((ui.value - 5000) / 2) + 1000;
           }
           console.log(user_reward);
           $("#reward2").html(user_reward);
       }
   });
   $( "#amount2" ).val($( "#slider-range-min2" ).slider( "value" ) + "р." );

   $( "#slider-range-min3" ).slider({
       range: "min",
       value: 8500,
       min: 7200,
       max: 15000,
       step: 100,
       slide: function( event, ui ) {
           $( "#amount3" ).val( ui.value );
           if(ui.value <= 8500){
               user_reward = ui.value - 7200;
           } else if(ui.value > 8500){
               user_reward = ((ui.value - 8500) / 2) + 1300;
           }
           console.log(user_reward);
           $("#reward3").html(user_reward);
       }
   });
   $( "#amount3" ).val($( "#slider-range-min3" ).slider( "value" ) + "р." );

   $( "#slider-range-min4" ).slider({
       range: "min",
       value: 1800,
       min: 1300,
       max: 3000,
       step: 100,
       slide: function( event, ui ) {
           $( "#amount4" ).val( ui.value );
            if(ui.value <= 1800){
                user_reward = ui.value - 1300;
            } else if(ui.value > 1800){
                user_reward = ((ui.value - 1800) / 2) + 500;
            }
            console.log(user_reward);
            $("#reward4").html(user_reward);
       }
   });
   $( "#amount4" ).val($( "#slider-range-min4" ).slider( "value" ) + "р." );

   $( "#slider-range-min5" ).slider({
       range: "min",
       value: 18500,
       min: 15500,
       max: 25000,
       step: 100,
       slide: function( event, ui ) {
           $( "#amount5" ).val( ui.value );
            if(ui.value <= 18500){
                user_reward = ui.value - 15500;
            } else if(ui.value > 18500){
                user_reward = ((ui.value - 18500) / 2) + 3000;
            }
            console.log(user_reward);
            $("#reward5").html(user_reward);
       }
   });
   $( "#amount5" ).val($( "#slider-range-min5" ).slider( "value" ) + "р." );

});







