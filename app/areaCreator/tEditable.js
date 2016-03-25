// Used to make td's editable (i.e. tables)
(function ( $ )
{
	$.fn.tEditable = function (url)
	{
		this.wrapInner('<input id="tEditable" type="text" />').children().val(this.text()).css('width','100%');
		ret = this.children();
		ret.focus();
		ret.blur(function ()
		{
			var $td = $(this).parent();
			var $tr = $td.parent();
			$td.text($("#tEditable").val());
			$.post(url, { 'id': $tr.attr('id'), 'column': $td.attr('title'), 'value': $td.text() }, function(data)
			{
				if (data == "OK")
				{
					notify("Updated Successfully!", {"status": "success"});
				}
				else
				{
					notify("Failed to update the server!<br /><div style=\"text-align: center;\">Please try again!</div>", {'status': 'failure', 'clickToDismiss': true});
				}
			});
		});
		return ret;
	}
}(jQuery));