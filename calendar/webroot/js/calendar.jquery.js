$(document).ready(function(){
   $(".calendar").delegate('#calendar-navigation span a','click',function(event){        
        event.preventDefault();
        $.getJSON(this.href,function(response){
           if(response.calendar){
               /**
                * We will have the entire content of "<div class='calendar'>" in the response.calendar,
                * so when we turn it into a jQuery object, and ask for the html() of that object
                * we will simply be returned everything in it,
                * including the new navigation (which we are bound above) and updated calendar content from the response
                *
                */
               var calendar = $(response.calendar);
               $(".calendar").html(calendar.html());
           }
        });
   });
});