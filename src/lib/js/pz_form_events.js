function loadForm(thisForm) {
	document.forms[thisForm].event.value = 'onLoad';
	//document.forms[thisForm].submit();
}

function runForm(thisForm) {
	//alert("Run form "+thisForm+".");
	document.forms[thisForm].event.value = 'onRun';
	//document.forms[thisForm].submit();
}

function unloadForm(thisForm) {
	document.forms[thisForm].event.value = 'onUnload';
	//document.forms[thisForm].submit();
}
