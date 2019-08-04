function validate_passwd(thisObject, comparedToThis, buttonToEnable) {
	//alert("toto");
	buttonToEnable.disabled = !(thisObject.value == comparedToThis.value);
}
