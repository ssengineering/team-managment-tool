(function($)
	{
		$.fn.piacenti = function(options)
		{
			return this.each(function()
			{
				var defaultOptions = $.extend(
				{
					width : 150,
					height : 0,
					css : "",
					direction : "right",
					up : 0,
					down : 0,
					left : 0,
					right : 0,
					timeDelay : 300,
					//trigger : ".trigger",
					popup : ".popup"		
				}, options);
				var opts = defaultOptions;

				//sets some position css in the elements to make the plugin work
				$(this).css("position", "relative");
				var comparison1 = $(this).find(opts.popup).css("position");
				var comparison2 = $(this).find(opts.popup).css("display");
				if (comparison1.search("absolute") == -1 || comparison2.search("none;") == -1)
				{
					
					if ( typeof InstallTrigger !== 'undefined')
					{
						
						$(this).find(".popup").css(
						{
							"position" : "relative",
							"display" : "none",
				
						});
					}
					else
					{
						$(this).find(".popup").css(
						{
							"position" : "absolute",
							"display" : "none"
						});
					}

				}
				
				//takes care of direction offsets, height has to be specified for up and down direction and width has to be specified for left and right
				var current = $(this).find(".popup");
				if (opts.direction == "right" && opts.left==0 && opts.right==0)
				{
					current.offset(
					{
						//top : opts.height + opts.down - opts.up
					});
				}
				else if (opts.direction == "right" && (opts.left!=0 || opts.right!=0))
				{
					current.offset(
					{
						left : opts.width/2 - opts.left + opts.right,
						top : -opts.height + opts.down - opts.up
					});
				}
				else if (opts.direction == "left")
				{
					current.offset(
					{
						left : -opts.width - opts.left + opts.right,
						top : opts.height + opts.down - opts.up
					});
				}
				else if (opts.direction == "up" )
				{
					current.offset(
					{
						left : - opts.left + opts.right,
						top : -opts.height + opts.down - opts.up
					});
				}
				else if (opts.direction == "down")
				{
					current.offset(
					{
						left : - opts.left + opts.right,
						top : opts.height/2 + opts.down - opts.up
					});
				}
				defaultCss = new Object();
				defaultCss['width'] = opts.width + 'px';
				defaultCss['background-color'] = "white";
				defaultCss['padding'] = "5px 10px 5px 10px";
				defaultCss['-moz-border-radius'] = "10px";
				defaultCss['-webkit-border-radius'] = "10px";
				defaultCss['border-radius'] = "10px";

				opts.css = opts.css.split(";");
				if (opts.css.length > 0)
				{
					for (x in opts.css)
					{

						opts.css[x] = opts.css[x].split(":");
						if (opts.css[x][0] != undefined && opts.css[x][1] != undefined)
						{
							defaultCss[(opts.css[x][0]).trim()] = (opts.css[x][1]).trim();
						}

					}
				}
				//console.log(defaultCss);
				current.css(defaultCss);


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
					}, opts.timeDelay);
					current.mouseover(function()
					{
						clearTimeout(delayTimer);
					});
				});
			});
		};
	}(jQuery));
