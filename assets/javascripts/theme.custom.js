/* Add here all your JS customizations */

(function( $ ) {

    'use strict';

    
    //Normaliza uma string para busca.
    function _wegiaNormalizeSearchString(data) {
        if (!data) {
            return '';
        }

        var str = typeof data === 'string' ? data : data.toString();

        if (typeof str.normalize === 'function') {
            return str
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();
        }

        return str.toLowerCase();
    }

    
    //Retorna o texto original e a versão normalizada juntos.
    function _wegiaNormalizeDataTablesSearchString(data) {
        if (!data) {
            return '';
        }

        var str = typeof data === 'string' ? data : data.toString();
        var normalized = _wegiaNormalizeSearchString(str);
        return normalized + ' ' + str;
    }

    
     //replica a normalização às APIs do DataTables/Select2.
    function _wegiaApplySearchNormalization() {
        var applied = false;

        // Normaliza os dados que o DataTables usa internamente para buscar.
        if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.type && $.fn.dataTable.ext.type.search) {
            applied = true;
            var search = $.fn.dataTable.ext.type.search;

            search.string = function (data) {
                return _wegiaNormalizeDataTablesSearchString(data);
            };

            search.html = function (data) {
                var text = $('<div/>').html(data).text();
                return _wegiaNormalizeDataTablesSearchString(text);
            };
        }

        // Normaliza o termo digitado no campo de busca do DataTables.
        if ($.fn.dataTable && $.fn.dataTable.Api && $.fn.dataTable.Api.prototype && $.fn.dataTable.Api.prototype.search) {
            applied = true;
            var originalSearch = $.fn.dataTable.Api.prototype.search;

            $.fn.dataTable.Api.prototype.search = function (input, regex, smart, caseInsensitive) {
                if (typeof input === 'string') {
                    input = _wegiaNormalizeSearchString(input);
                }
                return originalSearch.call(this, input, regex, smart, caseInsensitive);
            };
        }

        // Normaliza o matcher do Select2 (busca nos dropdowns).
        if ($.fn.select2 && $.fn.select2.defaults && $.fn.select2.defaults.set) {
            applied = true;
            $.fn.select2.defaults.set('matcher', function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }

                var term = _wegiaNormalizeSearchString(params.term);
                var text = _wegiaNormalizeSearchString(data.text || '');
                return text.indexOf(term) > -1 ? data : null;
            });
        }

        // Se já aplicamos, força o redraw para atualizar as tabelas existentes.
        if (applied && $.fn.dataTable && $.fn.dataTable.tables) {
            try {
                $($.fn.dataTable.tables()).each(function () {
                    var dt = $(this).DataTable();
                    if (dt && dt.rows) {
                        dt.rows().invalidate().draw(false);
                    }
                });
            } catch (e) {
                // ignore
            }
        }

        return applied;
    }

    // Aplica a normalização assim que toda a página estiver carregada (incluindo scripts).
    window.addEventListener('load', function () {
        _wegiaApplySearchNormalization();
    });

}).apply(this, [ jQuery ]);
