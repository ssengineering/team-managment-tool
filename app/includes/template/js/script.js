var clickOpened = false;

$(function()
{
	activateMenus();
	loadMenuImages();

	if (!Modernizr.borderradius)
	{
		var searchButton = $('#search-button');
		searchButton.attr('src', 'includes/template/img/search-button.png');
		searchButton.css('display', 'inline');
		$('#search').css('margin-right', '24px');
	}

	$(window).resize(positionFooter);
	positionFooter();
});

function positionFooter()
{
	$('#content').css('minHeight', ($(window).height() - $('header').height() - $('footer').height() - 50) + 'px');
}

function loadMenuImages()
{
	$('span.img').each(function()
	{
		var span = $(this);
		var url = span.removeClass('img').attr('class'), alt = span.attr('title');
		span.replaceWith('<img src="' + ( typeof webRoot === 'undefined' ? '' : webRoot + '/') + 'images/menu/' + url + '" alt="' + alt + '" />');
	});
}

function activateMenus()
{
	$('nav li:has(.mega, .sub) > a').click(function(e)
	{
		e.preventDefault();
		var li = $(this).parent();
		// Only close menu if user clicked to open it
		if (li.hasClass('hover') && clickOpened)
		{
			li.removeClass('hover');
		}
		else
		{
			li.addClass('hover');
			$('nav li').not(li).removeClass('hover');
			clickOpened = true;
		}
		return false;
	});

	$('nav li:has(.mega, .sub)').click(function(e)
	{
		e.stopPropagation();
	});

	// Menu config
	var byuMenuConfig =
	{
		over : rollOver, // function = onMouseOver callback (REQUIRED)
		timeout : 350, // number = milliseconds delay before onMouseOut
		out : rollOut // function = onMouseOut callback (REQUIRED)
	};
	$('#secondary-nav li, #primary-nav li').hoverIntent(byuMenuConfig);
	$('nav.no-js').removeClass('no-js');

	/* Positions menu divs */
	$('nav li .sub').each(function()
	{
		var mega = $(this);
		var left = mega.parent().position().left;
		if (left > mega.parent().parent().outerWidth() - mega.outerWidth())
			mega.css('right', 0);
	});
}

/* Func: RollOver
 * Desc: Show a dropdown menu on rollover. Called by the hoverIntent function.

 * Args: @evt	- Event object. Automatically generated.
 */
function rollOver(evt)
{

	if (!$(this).hasClass('hover'))
	{
		clickOpened = false;
		$(this).addClass('hover');
		$('nav li').not(this).removeClass('hover');
		$(document).click(hideAllMenus);
	}
	//if(evt !== undefined) evt.stopPropagation();
}

/* Func: RollOut
 * Desc: Hide a dropdown menu on rollout. Called by the hoverIntent function.
 * Args: -
 */
function rollOut()
{
	$(this).removeClass('hover');
}

/* Make the menu move down as you scroll down */
$(function()
{
	var offset = $("#primary-nav").offset();
	var height = $("#primary-nav").height();
	$(window).scroll(function()
	{
		var scrollTop = $(window).scrollTop();
		// check the visible top of the browser

		if (offset.top <= scrollTop)
		{
			var clone = uniqueClone("primary-nav");
			if ($("#primary-nav_Clone").length == 0)
			{
				$("#primary-nav").after(clone);
				$("#primary-nav_Clone").addClass("fixedMenu");
			}
		}
		else
		{
			$("#primary-nav_Clone").removeClass("fixedMenu");
			$("#primary-nav_Clone").remove();
		}

	});
});

function uniqueClone(id)
{
	var content = fullElementHtml(id);
	var clonedText = "";
	var id = false;
	var quote = false;

	for ( i = 0; i < content.length; i++)
	{
		clonedText += content[i];
		if (content[i] == "i")
		{
			if (content[i] + content[i + 1] + content[i + 2] == "id=" || content[i] + content[i + 1] + content[i + 2] + content[i + 3] == "id =")
			{
				id = true;
			}
		}
		else if (id == true && (content[i] == "\"" || content[i] == "\'"))
		{
			quote = !quote;
			//turns it to true at opening quotes and false at closing quotes
			if (quote == false)
			{
				id = false;
			}
		}
		else if (quote == true && (content[i + 1] == "\"" || content[i + 1] == "\'"))
		{
			clonedText += "_Clone";
		}
	}
	//console.log(clonedText);
	return clonedText;
}

function fullElementHtml(id)
{
	var text = $("#" + id).prop('outerHTML');
	return text;
}

/* Func: HideAllMenus
 * Desc: Hide all dropdown menus. Bound to click action.
 * Args: -
 */
function hideAllMenus()
{
	$('nav li').removeClass('hover');
	$(document).unbind('click');
}

/**
 * hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
 /* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
 * <http://cherne.net/brian/resources/jquery.hoverIntent.html>
 *
 * @param  f  onMouseOver function || An object with configuration options
 * @param  g  onMouseOut function  || Nothing (use configuration options object)
 * @author    Brian Cherne brian(at)cherne(dot)net
 */
(function($)
{
	$.fn.hoverIntent = function(f, g)
	{
		var cfg =
		{
			sensitivity : 7,
			interval : 100,
			timeout : 0
		};
		cfg = $.extend(cfg, g ?
		{
			over : f,
			out : g
		} : f);
		var cX, cY, pX, pY;
		var track = function(ev)
		{
			cX = ev.pageX;
			cY = ev.pageY
		};
		var compare = function(ev, ob)
		{
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			if ((Math.abs(pX - cX) + Math.abs(pY - cY)) < cfg.sensitivity)
			{
				$(ob).unbind("mousemove", track);
				ob.hoverIntent_s = 1;
				return cfg.over.apply(ob, [ev])
			}
			else
			{
				pX = cX;
				pY = cY;
				ob.hoverIntent_t = setTimeout(function()
				{
					compare(ev, ob)
				}, cfg.interval)
			}
		};
		var delay = function(ev, ob)
		{
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			ob.hoverIntent_s = 0;
			return cfg.out.apply(ob, [ev])
		};
		var handleHover = function(e)
		{
			var ev = jQuery.extend(
			{
			}, e);
			var ob = this;
			if (ob.hoverIntent_t)
			{
				ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t)
			}
			if (e.type == "mouseenter")
			{
				pX = ev.pageX;
				pY = ev.pageY;
				$(ob).bind("mousemove", track);
				if (ob.hoverIntent_s != 1)
				{
					ob.hoverIntent_t = setTimeout(function()
					{
						compare(ev, ob)
					}, cfg.interval)
				}
			}
			else
			{
				$(ob).unbind("mousemove", track);
				if (ob.hoverIntent_s == 1)
				{
					ob.hoverIntent_t = setTimeout(function()
					{
						delay(ev, ob)
					}, cfg.timeout)
				}
			}
		};
		return this.bind('mouseenter', handleHover).bind('mouseleave', handleHover)
	}
})(jQuery);
