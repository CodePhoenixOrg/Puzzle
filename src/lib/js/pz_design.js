var PZ_CUR_ROW_COLOR = "lightgrey";
var PZ_CUR_TEXT_COLOR = "black";
var PZ_DEF_HL_BACK_COLOR = "grey";
var PZ_DEF_HL_TEXT_COLOR = "white";
var PZ_CURRENT_EXPANDED_BLOCK = null;
var PZ_CURRENT_EXPANDED_BLOCK_NAME = "";
var PZ_CURRENT_BLOCK_SKIN = null;
var PZ_CURRENT_TAB_NAME = "";

function pz_shadow(thisName) {

	table = document.getElementById(thisName);
	shadow = document.getElementById(thisName + "_shadow");
	shadow_width = document.getElementById(thisName + "_sw");
	shadow_height = document.getElementById(thisName + "_sh");

	if (eval(shadow)) shadow.style.width = table.offsetWidth + 11 + "px";
	if (eval(shadow_width)) shadow_width.style.width = table.offsetWidth - 8 + "px";
	if (eval(shadow_height)) shadow_height.style.height = table.offsetHeight - 8 + "px";

}

function setRowColor(thisRow, hlBackColor, hlTextColor) {
	var id = thisRow.id;
	if (hlBackColor == "") hlBackColor = PZ_DEF_HL_BACK_COLOR;
	if (hlTextColor == "") hlTextColor = PZ_DEF_HL_TEXT_COLOR;
	PZ_CUR_ROW_COLOR = thisRow.cells[0].style.backgroundColor;
	var font = eval(document.getElementById("font_" + id + "0"));
	if (font !== undefined) PZ_CUR_TEXT_COLOR = font.style.color;
	CellCount = thisRow.cells.length;
	for (i = 0; i < CellCount; i++) {
		thisRow.cells[i].style.backgroundColor = hlBackColor;
		//thisRow.cells[i].style.fontColor=hlTextColor;
		font = eval(document.getElementById("font_" + id + i));
		if (font) font.color = hlTextColor;
	}
}

function setBackRowColor(thisRow) {
	var id = thisRow.id;
	CellCount = thisRow.cells.length;
	for (i = 0; i < CellCount; i++) {
		thisRow.cells[i].style.backgroundColor = PZ_CUR_ROW_COLOR;
		//thisRow.cells[i].style.fontColor=PZ_CUR_TEXT_COLOR;
		font = eval(document.getElementById("font_" + id + i));
		if (font) font.color = PZ_CUR_TEXT_COLOR;
	}
}

function display_tab(thisTab, tabCaptions) {
	var myTab;
	var curTab;
	if (!eval(thisTab)) return false;
	//alert("Onglet � rendre visible par d�faut : "+thisTab.id);

	for (i = 0; i < tabCaptions.length; i++) {
		curTab = eval(document.getElementById("tab_" + tabCaptions[i]));
		if (curTab) {
			//alert("Rendre invisible : "+curTab.id);
			curTab.style.display = "none";
			curTab.style.visibility = "visible";
			if (curTab.id == "tab_" + thisTab.id) {
				//alert(curTab.id+" = "+thisTab.id);
				myTab = curTab;
			}
		}
	}

	if (eval(myTab)) {
		//alert("Rendre visible : "+curTab.id);
		myTab.style.display = "inline";
		myTab.style.visibility = "visible";
		PZ_CURRENT_TAB_NAME = thisTab.id;
	}

	return true;
}

function expand_block(block, block_skin_name) {

	if (!eval(block)) return false;
	var bc_id = block.id;
	var i = 0;
	var bc = "block_caption_";
	var bt = "block_table_";
	var bc_index = bc_id.substr(bc.length, bc_id.length - bc.length);
	var btable = document.getElementById(bt + bc_index);
	//var skin_object=document.getElementById(skin_table_name);

	if (eval(PZ_CURRENT_EXPANDED_BLOCK)) {
		PZ_CURRENT_EXPANDED_BLOCK.style.display = "none";
		PZ_CURRENT_EXPANDED_BLOCK.style.visiblity = "visible";
		pz_shadow(PZ_CURRENT_BLOCK_SKIN);
	}

	btable.style.display = "inline";
	btable.style.visiblity = "visible";

	PZ_CURRENT_EXPANDED_BLOCK = btable;

	pz_shadow(block_skin_name);
	PZ_CURRENT_BLOCK_SKIN = block_skin_name;

	return true;
}

function get_radio_value(formName, groupName) {
	var myForm = document.forms[formName];
	if (!eval(myForm)) return -1;
	var myGroup = myForm.elements[groupName];
	if (!eval(myGroup)) return -1;

	var l = myGroup.length;
	var i = 0;
	var checked = false;

	while (i < l && checked == false) {
		checked = myGroup[i].status;
		i++;
	}

	return --i;
}

function setTextFromArray(myArray, myValue, myObjectName) {
	var myObject = document.getElementById(myObjectName);
	if (!eval(myObject)) {
		myObject = document.getElementsByName(myObjectName)[0];
		if (!eval(myObject)) return false;
	}

	var i = 0;
	var s = myArray.length;
	var found = false;
	while (!found && i < s) {
		found = myValue == myArray[i][0];
		i++;
	}
	//alert("value='"+myValue+"'");
	if (found) myObject.value = myArray[i - 1][1];

	return true;
}

function checkValue(myObjectName, myObjectLabel) {
	var myObject = document.getElementById(myObjectName);
	if (!eval(myObject)) {
		myObject = document.getElementsByName(myObjectName)[0];
		if (!eval(myObject)) return false;
	}

	alert("j'arrive l� 2");
	var strValue = myObject.value;

	if (strValue == "") {
		alert("Veuillez donner une valeur au champ '" + myObjectLabel + "'.");
		return false;
	}

	alert("j'arrive l� 3");
	return true;
}
