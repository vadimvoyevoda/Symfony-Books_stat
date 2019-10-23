(function($){

	$(document).ready( function(e){

		$(document).on("submit", "#search-form", function(e){
			e.preventDefault();
			$.ajax({
				url: $(this).attr("action"),
				type: $(this).attr("method"), 
				data: $(this).serialize(),
				success: function(data){
					$("#books_stat").html(data);
				}, 
				error: function(data){

				}
			});
		});

	});

})(jQuery);