
(function ($) {
    function fillquiz() {
        
        var selectedcourseid = $("#id_courseid").val();    
        var quizid = $("#id_quizid").val();  

            $.getJSON("./load_quiz.php?value="+selectedcourseid, function( data ) {
                
                var items = [];
                $.each( data, function( key, val ) {
                    if(quizid == key)
                        items.push( "<option value='" + key + "' selected>" + val + "</option>" );
                    else
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                });
                $(".fitem select[name='quizid']").html( items.join( "" ) );
            });     
    }
    function fillbadge() {
        
        var selectedcourseid = $("#id_courseid").val();    
        var badgeid = $("#id_badgeid").val();  
            $.getJSON("./load_badge.php?value="+selectedcourseid, function( data ) {
                
                var items = [];
                $.each( data, function( key, val ) {
                    if(badgeid == key)
                        items.push( "<option value='" + key + "' selected>" + val + "</option>" );
                    else
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                });
                $(".fitem select[name='badgeid']").html( items.join( "" ) );
            });     
    }
    
    $( document ).ready(function() {
        fillquiz();
        fillbadge();
        $("#id_courseid").change(function(){
                fillquiz();
                fillbadge();
        });
        
    });
}(jQuery));
