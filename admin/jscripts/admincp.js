var AdminCP = {
	init: function()
	{
	},

	deleteConfirmation: function(element, message)
	{
		if(!element) { return FALSE;
		}

		confirmReturn = confirm(message);
		if(confirmReturn == TRUE) {
			form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", element.href);
			form.setAttribute("style", "display: none;");
			document.getElementsByTagName("body")[0].appendChild(form);
			form.submit();
		}

		return FALSE;
	}
};

Event.observe(window, 'load', AdminCP.init);
