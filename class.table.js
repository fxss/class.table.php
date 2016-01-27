/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(window).load(function(event){
	
	$("#content").bind("DOMSubtreeModified", function() {
		
		$("table.class\\.table").hover(
			function(){
				$(this).find('.forToggle').show();
			},
			function(){
				$(this).find('.forToggle').hide();
			}
		);
		
		$("table.class\\.table .forToggle button").unbind('click');
		$("table.class\\.table .forToggle button").click(function() {
			$.get("/lib/class.table.php/getExcel.php", function(data) {
				if (data.length > 0)
					//alert(data);
					window.location = "/tmp/" + data;
			});
		});
		
	});
	
});