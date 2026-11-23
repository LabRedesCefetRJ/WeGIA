/*
 * Textos em português para as tabelas DataTables (1.10) do módulo Recepção.
 *
 * Inclua logo depois do jQuery e antes dos scripts que inicializam as tabelas: os textos são
 * aplicados nos padrões do DataTables no DOM-ready, antes de qualquer inicialização. Não passe
 * opções para DataTable() nas páginas que também carregam examples.datatables.default.js, porque a
 * tabela já está inicializada e o DataTables abre um alert de "Cannot reinitialise".
 */
window.DATATABLES_PT_BR = {
	sEmptyTable: "Nenhum registro encontrado",
	sInfo: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
	sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
	sInfoFiltered: "(filtrados de _MAX_ registros)",
	sLengthMenu: "_MENU_ registros por página",
	sLoadingRecords: "Carregando...",
	sProcessing: "Processando...",
	sZeroRecords: "Nenhum registro encontrado",
	sSearch: "",
	oPaginate: {
		sNext: "Próximo",
		sPrevious: "Anterior",
		sFirst: "Primeiro",
		sLast: "Último"
	},
	oAria: {
		sSortAscending: ": ordenar em ordem crescente",
		sSortDescending: ": ordenar em ordem decrescente"
	}
};

jQuery(function ($) {
	if ($.fn.dataTable) {
		$.extend(true, $.fn.dataTable.defaults, { oLanguage: window.DATATABLES_PT_BR });
	}
});
