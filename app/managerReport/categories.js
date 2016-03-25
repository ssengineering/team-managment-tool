/**
 * Creates a dialog for editing the manager report categories
 */
function showCategoryEditorPopup(){
	$("#category-manager").dialog({
		resizable: false,
		width: 300,
		modal: true,
		draggable: true,
		title: "Edit Categories"
	   });//dialog
	$("#new-category").focus();
}

/**
 * Loads the current list of categories through the API,
 * then refills the lists in the dropdown menu, the search 
 * options, and the category editor.
 */
function loadCategories(){
	$.ajax({
		url : "/API/managerReportCategories",
		type: "GET",
		dataType: "json",
		data: {
			'type' : 'getAll'			
		}
	}).done(function(response){
		if (!response) {
			console.log("Failed to load categories");
			return;
		}
		else{
			// Reset the search options box
			$("#categories").empty();
			$("#categories").append("<input id='searchCategories' type='checkbox' "
				+"onclick='checkContent(\"searchCategories\")' checked='checked'><i> All</i><br>");
			
			// Reset the new entry category dropdown
			$("#newEntryCategory").empty();
			$("#newEntryCategory").append("<option value='0' disabled selected>Categories</option>");
			
			// Reset the category editor
			$("#category-list").empty();
			
			var containsInactive = false;
			for (var i=0; i < response.length; i++){
				var category = response[i];
				// Fill search option list
				$("#categories").append('<input id="searchCategories_' + category['category'].replace(" ", "_")+
					'" class="searchCategory" type="checkbox"  value="' +category['id']+ 
					'" name="' +category['category']+ '" checked="checked"><label id="searchLabel_'+category['id']+'">'+category['category']+'<br>');
				if (category['active'] == "0") {
					$('#searchLabel_' + category['id']).addClass("warning");
					containsInactive = true;
					continue;
				}
				
				// Fill new category dropdown
				$("#newEntryCategory").append('<option value="'+category['id']+ '">' +category['category']+ '</option>');
				
				// Fill category editor
				$("#category-list").append('<input type="text" id="edit-category-'+category['id']+
					'" class="category-name" value="'+category['category']+'" /><span class="deactivate-category" id="deactivate-category-'+category['id']+'">X</span>');
				
				// Set triggers to edit and deactivate categories
				$('#edit-category-'+category['id']).change({id: category['id']}, updateCategory);
				$('#deactivate-category-'+category['id']).click({id: category['id'], category: category['category']}, removeCategory);
			}
			
			if (containsInactive){
				$("#categories").prepend("<p class='warning'>Categories that are no longer active are listed in red</p>")
			}
		}
		});
	
	// Set trigger for adding categories
	// Responds to plus button and the enter key inside the text input
	$("#add-category").click(function(){
		addCategory($("#new-category").val());
		$("#new-category").val("");
		loadCategories();
	});
	$(document).keyup(function (e) {
		if ($("#add-category:focus") && (e.keyCode === 13)) {
		   addCategory($("#new-category").val());
			$("#new-category").val("");
			loadCategories();
		}
 	});
}

/**
 * Reads the new category name and updates the database
 * 
 * @param event
 * 		event.data.id contains the id of the category to be updated
 */
function updateCategory(event){
	var updatedName = $('#edit-category-'+event.data.id).val();
	editCategory(event.data.id, updatedName);
}


/**
 * Checks if there have ever been any reports submitted with that
 * category name. If yes, it will confirm that the user really wants
 * to deactivate the category, then deactivate it. If no, the category 
 * will be removed without the alert.
 *  
 * @param event
 * 		event.data is an object containing both the name and id
 * 		of the category to be removed
 */
function removeCategory(event){
	$.ajax({
		url : "/API/managerReportCategories",
		type: "GET",
		dataType: "json",
		data: {
			'type' : 'checkDeleteImpact',
			'id' : event.data.id			
		}		
	}).done(function(response){
		var numEntries = response;
		if(numEntries > 0){
			var confirmRemove = confirm("Are you sure you want to deactivate the category, '"+event.data.category+"'? There have been "+numEntries+" reports submitted so far under this category. \n\nTo re-enable the category, simply add a new category with the same name.");
			if(!confirmRemove){
				return;
			}
			deactivateCategory(event.data.id);	
		}
		deactivateCategory(event.data.id);
	});
	
}

/**
 * Submits request to the API to deactivate a category.
 * 
 * @param id
 * 		the id of the category being deactivated.
 */
function deactivateCategory(id, reload){
	$.ajax({
		url : "/API/managerReportCategories",
		type: "GET",
		dataType: "json",
		data: {
			'type' : 'deactivate',
			'id' : id			
		}
	}).done(function(response){
		if(response){
			getReport();
			loadCategories();
		} else {
			console.log("Failed to deactivate category");		
		}
	});
}

/**
 * Submits request to the API to add a category.
 * 
 * @param category
 * 		name of category to be added
 */
function addCategory(category){
	if (category.trim() == ""){
		return;
	}
	$.ajax({
		url : "/API/managerReportCategories",
		type: "GET",
		dataType: "json",
		data: {
			'type' : 'add',
			'name' : category			
		}
	}).done(function(response){
		if(response){
			loadCategories();
		} else {
			console.log("Failed to add category");		
		}
	});
}

/**
 * Submits a request to the API to update the category name
 * 
 * @param id
 * 		the id of the category to update
 * @param category
 * 		the new name of the category
 */
function editCategory(id, category){
	$.ajax({
		url : "/API/managerReportCategories",
		type: "GET",
		dataType: "json",
		data: {
			'type' : 'edit',
			'name' : category,
			'id' : id			
		}
	}).done(function(response){
		if(response){
			loadCategories();
		} else {
			console.log("Failed to update category");		
		}
	});
}
