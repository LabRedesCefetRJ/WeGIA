/*
Name: 			Tables / Advanced - Examples
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version: 	1.3.0
*/

(function( $ ) {
	'use strict';

	var removeAccents = function(value) {
		if (value === null || value === undefined) {
			return '';
		}

		var text = String(value).toLowerCase();

		if (typeof text.normalize === 'function') {
			return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		}

		return text;
	};

	if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.type && $.fn.dataTable.ext.type.search) {
		$.fn.dataTable.ext.type.search.string = function(data) {
			return removeAccents(data);
		};

		$.fn.dataTable.ext.type.search.html = function(data) {
			return removeAccents(data);
		};
	}

	var datatableInit = function() {
		var $table = $('#datatable-default');

		if ($table.length && !$.fn.DataTable.isDataTable($table)) {
			$table.dataTable();
		}
	};

	$(function() {
		datatableInit();
	});

}).apply( this, [ jQuery ]);