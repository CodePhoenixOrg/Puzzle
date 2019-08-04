function pz_getPath(thisObject) {
	//var p="document.all(\""+thisObject+"\")";
	var p = "document.getElementById(\"" + thisObject + "\")";
	if (eval(p))
		return p;
	else
		return false;
}

function pz_elements() {
	var i; var j;

	j = document.all.length;
	for (i = 0; i < j; i++) {
		alert(document.all[i].tagName + "<br>");
	}
}

/*
	function pz_getTDStyle(thisTD) {
		var i; var j;

		//var myTD=document.getElementById(thisTD);
		//var myTD=document.all.tdapplis0;
		//if(!myTD) return false;

		//while(foundTD.id<>ThisTD)

		alert("coucou");
		var parent=document.all.tdapplis0.parentElement;
		while(parent) {
			alert("parent:'"+parent.tagName+"'");
			parent=parent.parentElement;
		}
		return true;
	}
*/
