var PZ_LOAD_SPLASH = document.getElementById("load_splash");

//alert("spLeft='"+spLeft+"'; splash='"+splash+"'");

function loadSplashOn() {
	splash = PZ_LOAD_SPLASH;
	if (!eval(splash)) return false;
	//alert("show");
	var spLeft = Math.round((screen.width / 2) - 200);
	splash.style.left = spLeft + "px";
	splash.style.visibility = "visible";
	return true;
}
/*
function loadSplashOff() {
	splash=PZ_LOAD_SPLASH;
	if(!eval(splash)) return false;
	//alert("hide");
	splash.style.visibility = "hidden";
	return true;
}
*/
