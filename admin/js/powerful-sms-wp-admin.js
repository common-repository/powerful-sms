(function( $ ) {
	if ($( "#accordionTriggers" ).length > 0 ) {
        var codedefault = $('#countryCode').attr('data-default');
        $('#countryCode').val(codedefault);
        $( "#accordionTriggers" ).accordion({
          heightStyle: "content"
        });
        $('#accordionTriggers input[type="checkbox"]').click(function(e) {
            e.stopPropagation();
        });
    }


})( jQuery );
