/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(window).load(function(event){
	
	$("#content").bind("DOMSubtreeModified", function() {
		
		$("table.class\\.table .forToggle button").unbind('click');
		$("table.class\\.table .forToggle button").click(function() {
			var group = 1*($(this).parent().find("input:checkbox").is(':checked'));
			
			$.get("/lib/class.table.php/getExcel.php", {group: group}, function(data) {
				if (data.length > 0) {
					data = data.split("###");
					var msg = data[0];
					var table = data[1];
					
					if (msg.length > 0)
						alert(msg);
					
					if (table.length > 0)
						window.location = "/tmp/" + table;
				}
			});
		});
		
	});
	
});