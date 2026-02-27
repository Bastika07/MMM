function SendMail(praefix) {
	var toplevel = String.fromCharCode(46,100,101);
	var domain =  String.fromCharCode(109,117,108,116,105,109,97,100,110,101,115,115);
	var at =  String.fromCharCode(64);
	location.href = "mail" + "to:" + praefix + at + domain + toplevel;
}