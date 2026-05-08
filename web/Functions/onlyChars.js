	function Onlychars(e)
	{
		var tecla=new Number();
		if(window.event) {
			tecla = e.keyCode;
		}
		else if(e.which) {
			tecla = e.which;
		}
		else {
			return true;
		}
		if((tecla >= "48") && (tecla <= "57")){
			return false;
		}
	}