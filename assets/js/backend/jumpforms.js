jQuery(function($) {
	
	$('table#jumpforms').tableDnD({
	onDragClass: "dragging",
	onDrop: function(table, row) {
		var rows = table.tBodies[0].rows;
		var debugStr = "";
		for (var i=0; i<rows.length; i++) {
		debugStr += rows[i].id+",";
		
		}
			debugStr = debugStr.slice(0, -1);
			$('.sortorder').val(debugStr);
	}	
	
	});

    $("table#jumpforms tr").hover(function() {
          $(this.cells[0]).addClass('candrag');
    }, function() {
          $(this.cells[0]).removeClass('candrag');
    });
    
	$(".trigger").click(function(){
	    $( $(this).find("a").attr('href') ).slideToggle("slow");
	  });
   
});