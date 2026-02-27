function openSearch1() {
  userId = document.forms.ticketForm.iUserId.value;
  detail = window.open(
    "benutzerverwaltung.php?iDest=iUserId&action=search",
    "Bestellung",
    "width=780,height=520,locationbar=false,resize=false");
  detail.focus();
}

function openSearch2() {
  userId = document.forms.ticketForm.iZuordnung_UserId.value;
  detail = window.open(
    "benutzerverwaltung.php?iDest=iZuordnung_UserId&action=search",
    "Bestellung",
    "width=780,height=520,locationbar=false,resize=false");
  detail.focus();
}

function openAbendkasse(nPartyID, naechstePartyID) {
  userId = document.forms.ticketForm.iUserId.value;
  
  if (userId == "" || userId <= 0) {
	  alert("Bitte eine Benutzer-ID angeben.");
	  return false;
  }
  
  url = "tickets_checkin_abendkasse_order.php?action=abendkasse&nPartyID="+nPartyID+"&iId=" + userId;
  if (naechstePartyID > 0) url = url + "&VVKpartyNummerID=" + naechstePartyID;
  detail = window.open(
    url,
    "Abendkasse",
    "width=780,height=520,locationbar=false,resize=false");
  detail.focus();
}

function openExtrazuordnung(nPartyID) {
  userId = document.forms.ticketForm.iUserId.value;
  detail = window.open(
    "tickets_checkin_abendkasse_extrazuordnung.php?nPartyID="+nPartyID+"&iId=" + userId,
    "Extrazuordnung",
    "width=620,height=420,locationbar=false,resize=false");
  detail.focus();
}

function xmlhttpPost(value) {
  if (value.length < 3) {
    return;
  }

  var http_request;
  if (window.XMLHttpRequest) { // Mozilla, Safari, ...
    http_request = new XMLHttpRequest();
  } else if (window.ActiveXObject) { // IE
    http_request = new ActiveXObject('Microsoft.XMLHTTP');
  }
  http_request.onreadystatechange = function() {
    if (http_request.readyState == 4) {
      updatepage(http_request.responseXML);
    }
  }
  http_request.open('GET', 'user_search_xml.php?query=' + value);        
  http_request.send(null);
}

function getquerystring() {
  var value = document.getElementById('nameInput').value;
  return escape(value);
}

function updatepage(response) {        
  var users = response.getElementsByTagName('user');
  document.getElementById('userSelect').options.length = 0;
  var user;
  for (i = 0; i < users.length; i++) {
    user = users.item(i);
    document.getElementById('userSelect').options[i] = new Option(user.childNodes[0].nodeValue, user.attributes['id'].value);
  }
}
