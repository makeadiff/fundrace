function init() {
	$("#tasks input").change(function() {
		var id = this.value;
		var status = this.checked;
		
		$.ajax({
				"url": "ajax/task_status_change.php?task_id="+id+"&status="+status,
				"dataType":"json"
			}).done(function(data) {
				$("#task-progress").css("width", data.task_done_percentage + "%");
				$("#task-progress").html(data.task_done_percentage + "%");
			});
	});
}
