// var corCompleta = "#99ff8f"
// var corIncompleta = "#eff70b"

function ResetCampos(){
    var textFields = document.getElementsByTagName("input");
        for (let i=0; i < textFields.length; i++){
        if(textFields[i].type == "text"){
            textFields[i].style.backgroundColor = "";
            textFields[i].style.borderColor = "";
        }
    }   
}

function coresMask(t){
	const l = t.value;
	const m = l.length;
	if(m === 0){
		t.style.borderColor = "";
		t.style.backgroundColor = "";
	}
}

function mascara(m,t,e,c){
	let cursor = t.selectionStart;
	let texto = t.value;
	texto = texto.replace(/\D/g,'');
	const l = texto.length;
	const lm = m.length;

	let tecla;
	if(window.event) {                  
	    tecla = e.keyCode;
	} else if(e.which){                 
	    id = e.which;
	}
	
	const TECLA_BACKSPACE = 8;
    const TECLA_DELETE = 46;
    const TECLA_SHIFT = 16;
    const TECLA_PAUSE = 19;
    const TECLAS_NAVEGACAO_INICIO = 33; 
    const TECLAS_NAVEGACAO_FIM = 40;
    

    if ((tecla === TECLA_BACKSPACE || tecla === TECLA_DELETE) && t.selectionStart === 0 && t.selectionEnd === t.value.length) {
        t.value = '';
        return;
    }
    
    let cursorfixo = false;
    if (cursor < l) cursorfixo = true;
    
    let livre = false;
    if (tecla === TECLA_SHIFT || tecla === TECLA_PAUSE || (tecla >= TECLAS_NAVEGACAO_INICIO && tecla <= TECLAS_NAVEGACAO_FIM)) {
        livre = true;
    }
    
    
    if (!livre) {
        if (tecla !== TECLA_BACKSPACE) {
            t.value = "";
            let j = 0;
            for (let i = 0; i < lm; i++) {
                if (m.substr(i, 1) === "#") {
                    t.value += texto.substr(j, 1);
                    j++;
                } else if (m.substr(i, 1) !== "#") {
                    t.value += m.substr(i, 1);
                }
                if (tecla !== TECLA_BACKSPACE && !cursorfixo) cursor++;
                if (j === l + 1) break;
            }   
        }
        if (c) coresMask(t);
    }
    
    if (cursorfixo && !livre) cursor--;
    t.setSelectionRange(cursor, cursor);
}