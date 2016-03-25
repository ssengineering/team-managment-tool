window.onload = function()
{

	$("#sortOrder").find("input[style]").css("display", "none");
	enableDragDrop();
	bindOnClick();
	showHide();
	$(":button").css(
	{
		"height" : 20,
		"padding-top" : 0
	})
	$(":button").button();
	renameInternal();
}
function enableDragDrop()
{
	$("div[onclick*='togglediv']").each(function()
	{
		var onclickContent = $(this).attr('onclick') + "; flipArrows('" + $(this).attr('id') + "span')";
		$(this).before("<span style='display: inline;float:left;' class='ui-icon ui-icon-triangle-1-e' id='" + $(this).attr('id') + "span' onclick=\"" + onclickContent + "\"></span>");
		$(this).removeAttr("onclick");

	});

	$(".sortList").sortable();
}

function reorder(id)
{
	var count = 0;

	if ($(id).is("tr") == false)
	{
		setTimeout(function()
		{
			$(id).parent().children().children("input[style]").each(function()
			{

				$(this).attr("value", count);
				count++;
			}), 300
		});
	}
	if ($(id).is("tr") == true)
	{
		setTimeout(function()
		{
			$(id).parent().find("input[style]").each(function()
			{

				$(this).attr("value", count);
				count++;
			}), 300
		});
	}
}

function bindOnClick()
{

	$(".sortList").find("li").each(function()
	{
		$(this).bind("mouseup", function()
		{
			reorder(this);
		});
	});
}

function flipArrows(id)
{

	if ($("#" + id).hasClass("ui-icon-triangle-1-e") == true)
	{
		$("#" + id).attr("class", "ui-icon ui-icon-triangle-1-s");
	}
	else
	{
		$("#" + id).attr("class", "ui-icon ui-icon-triangle-1-e");
	}
}
//Not Currently in use
function changeName(element, old_title, action)
{
	function helper(element)
	{
		$(element).replaceWith(global_temp);
		$(element).unwrap();
		$("span[onclick*='changeName']").remove();
		global_temp = "";
		renameInternal(element);
	}

	var new_text = $(element).val();
	if (new_text != old_title && action == "rename")
	{
		$.post('rename.php',
		{
			"old_title" : old_title,
			"new_title" : new_text
		}, function(data)
		{
			if (data == "error")
			{
				alert("An error occurred, the name was not changed");
				helper(element);
			}
			else
			{
				global_temp = global_temp.replace(old_title, new_text);
				helper(element);
			}
		});
	}
	else
	{
		helper(element);
	}
}

var global_temp;
function renameInternal(id)
{
	if (id == undefined)
	{
		id = ".rename";
	}

	$(id).each(function()
	{
		$(this).dblclick(function()
		{
			if ($(":input[id*='title']").length == 0 && $(":input[id*='span']").length == 0)//only allow renaming of one area at a time to avoid creating duplicate ids
			{
				global_temp = $(this).wrap("<span>").parent().html();
				$(this).replaceWith("<input type='text' style='display: inline;' class='rename' id='" + $(this).attr('id') + "' value='" + $(this).text() + "'>");
				$("#" + $(this).attr('id')).after("<span class='arrow-left'  style='color:white; border-right:1px solid white; padding:10px 10px 10px 5px;margin-left:15px;' onclick='changeName(\"#" + $(this).attr('id') + "\", \"" + $(this).text() + "\",  \"rename\")'>Click to Rename</span>" + "<span class='optionSelect' style='color:white;padding:10px 10px 10px 5px;background-color: #011948;' onclick='changeName(\"#" + $(this).attr('id') + "\", \"" + $(this).text() + "\",  \"cancel\")'>   Cancel</span>")
			}
		});
	});
}

function showHide()
{
	$(".trigger").each(function()
	{
		var current = $("#" + "span" + ($(this).attr("index")));
		$(this).css("position", "relative");
		current.css(
		{
			"position" : "absolute",
			"width" : "150px"
		});
		$(this).bind("mouseenter", function()
		{
			$(".popup").css("display", "none");
			if (current.css("display") == "none")
			{
				current.show("fast");
			}

		});
		$(this).bind("mouseout", function()
		{

			var delayTimer = setTimeout(function()
			{
				current.css("display", "none");
			}, 300);
			current.mouseover(function()
			{
				clearTimeout(delayTimer);
			});
			current.mouseleave(function()
			{
				setTimeout(function()
				{
					current.css("display", "none");
				}, 300);
			});
		});
	});

}
