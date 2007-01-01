rollover=function()
{
	if (document.all&&document.getElementById)
	{
		
		var nav=document.getElementById("nav").getElementsByTagName("LI");
				
		for (i=0; i<nav.length; i++)
		{
			
			nav[i].onmouseover=function()
			{
				this.className+=" dropped";
			}
			nav[i].onmouseout=function()
			{
				this.className=this.className.replace(" dropped", "");
			}
			
		}
	}

}

if (window.attachEvent) window.attachEvent("onload", rollover);