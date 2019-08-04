function addNoneItem(list) {
	if (!eval(list)) return false;

	if (list.length == 0) list.options[0] = new Option("Aucun", 0, false, true);

	return true;
}

function addToList(option, list) {
	if (!eval(option)) return false;
	if (!eval(list)) return false;

	ll = list.options.length;
	//alert(ll);
	if (ll == 1) {
		if (list.options[0].text == "Aucun") {
			list.options[0] = null;
			ll = 0;
		}
	}
	var text = option[option.selectedIndex].text;

	list.options[ll] = new Option(text, option.value, false, true);

	return true;
}

function removeFromList(list) {
	if (!eval(list)) return false;

	ll = list.options.length - 1;

	for (i = ll; i > -1; i--) {
		if (list.options[i].selected) list.options[i] = null;
	}

	addNoneItem(list);

	return true;
}

//Sélectionner tous les éléments d'une liste
function selectOptions(list) {
	if (!eval(list)) return false;

	ll = list.options.length;
	for (i = 0; i < ll; i++) {
		list.options[i].selected = true;
	}

	return true;
}

//Ajouter l'�l�ment 'Aucun' � toutes listes si elles sont vides
function addAllNoneItem() {
	addNoneItem(lib_jrn_m);
	addNoneItem(lib_ori_m);
	addNoneItem(lib_rub_m);
	addNoneItem(lib_tpr_m);
	addNoneItem(lib_gro_m);
	addNoneItem(lib_jfo_m);
	addNoneItem(lib_per_m);
}

//S�lectionner tous les �l�ments de toutes les listes du formulaire
function selectAll() {
	selectOptions(lib_jrn_m);
	selectOptions(lib_ori_m);
	selectOptions(lib_rub_m);
	selectOptions(lib_tpr_m);
	selectOptions(lib_gro_m);
	selectOptions(lib_jfo_m);
	selectOptions(lib_per_m);
}

function init() {
	var waitAlert = "<div id='waitAlert' style='top:200px; left:200px; width:300px; height:50px;'>";
	waitAlert += "<table border='2' bordercolor='black' cellpadding='0' cellspacing='0'>";
	waitAlert += "<tr><td>";
	waitAlert += "<h1>Veuillez patienter ...</h1>";
	waitAlert += "</td></td>";
	waitAlert += "</table>";
	waitAlert += "</div>";

	//document.write(waitAlert);
}
