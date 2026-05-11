	function Onlychars(e)
	{
		if (e && (e.ctrlKey || e.metaKey || e.altKey)) {
			return true;
		}

		if (e && e.key) {
			if (e.key === 'Dead' || e.isComposing) {
				return true;
			}

			if (/^\d$/.test(e.key)) {
				return false;
			}

			return true;
		}

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

(function carregarValidadorDeNome() {
	if (typeof document === 'undefined' || typeof validarNome === 'function') {
		return;
	}

	var scriptAtual = document.currentScript;
	if (!scriptAtual || !scriptAtual.src) {
		return;
	}

	var script = document.createElement('script');
	script.src = scriptAtual.src.replace(/onlyChars\.js(?:\?.*)?$/, 'valida_nome.js');
	document.head.appendChild(script);
})();
