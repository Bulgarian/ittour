jQueryMod2(function(jQueryMod2){
	jQueryMod2.datepicker.regional['ru'] = {
		closeText: '�������',
		prevText: '&#x3C;����',
		nextText: '����&#x3E;',
		currentText: '�������',
		monthNames: ['������','�������','����','������','���','����',
		'����','������','��������','�������','������','�������'],
		monthNamesShort: ['���','���','���','���','���','���',
		'���','���','���','���','���','���'],
		dayNames: ['�����������','�����������','�������','�����','�������','�������','�������'],
		dayNamesShort: ['���','���','���','���','���','���','���'],
		dayNamesMin: ['��','��','��','��','��','��','��'],
		weekHeader: '���',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	jQueryMod2.datepicker.setDefaults(jQueryMod2.datepicker.regional['ru']);
});