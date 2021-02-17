var AdminCP = {
	init: function()
	{
	},

	deleteConfirmation: function(element, message)
	{
		if(!element) return false;
		confirmReturn = confirm(message);
		if(confirmReturn == TRUE)
		{
			form = $("<form />", { method: "post", action: element.href, style: "display: none;" });
			$("body").append(form);
			form.trigger('submit');
		}
		return false;
	}
};

$(function()
{
	AdminCP.init();
});
