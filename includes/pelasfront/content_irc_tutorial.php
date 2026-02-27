
<!-- ANFANG Allgemeine Konfiguration -->

<h2>Schritt 1: Allgemeine Konfiguration</h2><p>

Nach der Installation von <a href="http://www.mirc.co.uk/">mirc</a> seht Ihr als Erstes diesen Screen. (siehe Bild 1)<br>
Dort f&uuml;llt Ihr als erstes die folgenden Felder aus:

<p>
  <table border="1" rules="groups">
    <thead>
      <td width="90" height="5" nowrap></td>
      <td width="160" height="5" nowrap><b>Eintrag</b></td>
      <td width="280" height="5" nowrap><b>Erkl&auml;rung</b></td>
    </thead>
    <tbody>
    <tr>
      <td><i>Full Name<i></td>
      <td>Euer Name</td>
      <td>Der Name erscheint bei einem Whois als Realname.</td>
    </tr>
    <tr>
      <td><i>Email Adress</i></td>
      <td>Eure Email Adresse</td>
      <td>trags einfach ein</td>
    </tr>
    <tr>
      <td><i>Nickname</i></td>
      <td>Euer Nickname im Chat</td>
      <td>Der Nickname mit dem Ihr euch im IRC "bewegt".</td>
    </tr>
    <tr>
      <td><i>Alternative</i></td>
      <td>Zweiter Nickname</td>
      <td>Falls der erste Nickname den Ihr gew&auml;hlt habt vergeben ist, wird dieser genommen.</td>
    </tbody>
    <tfoot>
    <tr>
      <td></td>
      <td><b>Eintrag</b></td>
      <td><b>Erkl&auml;rung</b></td>
    </tr>
    </tfoot>
  </table>
<p>
    <img src="/gfx_all/images_irc_tutorial/bild1.JPG"><p>

Falls Ihr den Screen nicht sehen solltet k&ouml;nnt Ihr ihn über das Menu <i>Tools - Options</i> oder den Shortcut <i>Alt+O</i> erreichen.<p>

<!-- Einstellungen der Tabelle von nadinchen mail to: naddel@quietscht.net, liebe dich kleene! -->
<!-- ENDE Allgemeine Konfiguration -->

<hr width="50%">

<!-- ANFANG Server Konfiguration -->

<h2>Schritt 2: Server Konfiguration</h2><p>

Als N&auml;chstes klickt Ihr auf das "sub menu" <i>Servers</i> und klickt dort auf <i>Add</i>. (siehe Bild 2 und Bild 3)<p>

<table border="1" rules="groups">
    <thead>
      <td width="90" height="5" nowrap></td>
      <td width="160" height="5" nowrap><b>Eintrag</b></td>
      <td width="280" height="5" nowrap><b>Erkl&auml;rung</b></td>
    </thead>
    <tbody>
    <tr>
      <td><i>Description<i></td>
      <td>innovaLAN-IRC</td>
      <td>Die Beschreibung des IRC Servers, in unserem Falle <i>innovaLAN-IRC</i> genannt.</td>
    </tr>
    <tr>
      <td><i>IRC Server</i></td>
      <td>irc.lan</td>
      <td>Die Adresse des IRC Servers, in unserem Falle ist dies <i>irc.lan</i></td>
    </tr>
    <tr>
      <td><i>Port(s)</i></td>
      <td>Standard 6667</td>
      <td>Der Port des Servers, am besten auf Standard belassen.</td>
    </tr>
    <tr>
      <td><i>Group</i></td>
      <td>innovaLAN</td>
      <td>Erstellt eine neue Gruppe in der Spalte <i>IRC Network</i> (siehe Bild 2). Dazu sp&auml;ter mehr.</td>
    </tr>
    <tr>
      <td><i>Password</i></td>
      <td>Kein Eintrag erforderlich</td>
      <td>Wird für Password gesch&uuml;tzte IRC Server ben&ouml;tigt.</td>
    </tbody>
    <tfoot>
    <tr>
      <td></td>
      <td><b>Eintrag</b></td>
      <td><b>Erkl&auml;rung</b></td>
    </tr>
    </tfoot>
  </table>
<p>
Wenn Ihr alles so eingestellt habt sollte das Ganze danach etwa so aussehen:

<table border="0">
    <tr>
      <td><b>vorher</b></td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/bild2.JPG"></td>
    </tr>
    <tr>
      <td><b>nachher</b></td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/fertig_server.JPG"></td>
    </tr>
    <tr>
      <td><b>vorher</b></td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/bild3.JPG"></td>
    </tr>
    <tr>
      <td><b>nachher</b></td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/fertig_add.JPG"></td>
    </tr>

</table>
<p>

Danach solltet Ihr dem Menu <i>Perform</i> unter <i>Connect - Options</i> (siehe Bild 4) Eure Aufmerksamkeit widmen.<br>
Dort k&ouml;nnt Ihr die Chan's einstellen die beim connect ins IRC automatisch gejoined werden sollen.<br>
Das Ganze macht Ihr so:<p>

<ul>
  <li>Als Erstes klickt Ihr auf <i>Perform...</i> (siehe Bild 4), danach macht Ihr einen Haken bei <i>Enable Perform on Connect</i> (siehe Bild 5).<br>
      Danach stellt Ihr im Auswahl Menu auf <i>Other Networks</i> (Bild 5) und dr&uuml;ckt auf <i>Add</i>.</li>
  <li>Dort angekommen w&auml;hlt Ihr als Netz <i>innovaLAN</i> (siehe Bild 6) aus. Damit Chans aus dem Perform auch nur beim connect ins <i>innovaLAN-IRC</i> gejoined werden.</li>
  <li>Nach dem best&auml;tigen mit <i>OK</i> tragt Ihr in das Feld <i>Perform Commands</i> (siehe Bild 7) die Chans ein in die Ihr automatisch connecten wollt.<br>
      Hier schonmal eingetragen <i>#Northcon</i> (der Main Channel) und zwei Turnier Channel. (eine Komplette Liste der Turnier Channel findet Ihr am Ende).</li>
</ul>

  <img src="/gfx_all/images_irc_tutorial/bild4.JPG"><br>
  <img src="/gfx_all/images_irc_tutorial/bild5.JPG"><br>
  <img src="/gfx_all/images_irc_tutorial/bild6.JPG"><br>
  <img src="/gfx_all/images_irc_tutorial/bild7.JPG"><br>

<p>

<!-- ENDE Server Konfiguration -->

<hr width="50%">

<!-- ANFANG Zusätzliche Konfiguration -->

<h2>Schritt 3: Zus&auml;zliche Konfigurationen</h2><p>

Zus&auml;tzlich solltet Ihr noch folgenden Funktionen aktivieren:<p>

<table border="0">
    <tr>
      <td>Unter <i>IRC - Messages</i> die <i>Timestamp events</i> aktivieren,</td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/zusatz_1.JPG"></td>
    </tr>
    <tr>
      <td>Als N&auml;chstes solltet Ihr unter <i>IRC - Logging</i> noch das logging auf <i>Both</i> stellen.</td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/zusatz_3.JPG"></td>
    </tr>
    <tr>
      <td>und unter <i>IRC - Catcher</i> das <i>url &amp; email catching<i>.</td>
    </tr>
    <tr>
      <td><img src="/gfx_all/images_irc_tutorial/zusatz_2.JPG"></td>
    </tr>
    <tr>
      <td align="center"><b>Erkl&auml;rung</b>:</td>
    </tr>
    <tr>
      <td><ul>
        <li><i>Timestamp events:</i> Durch diese Funktion wird euch die Uhrzeit vor jedem Post im IRC angezeigt.</li>
	<li><i>Url &amp; email catcher:</i> Durch diese Funktion werden <i>links</i> direkt mit dem Browser ge&ouml;ffnet.</li>
	<li><i>Logging:</i> Durch <i>Both</i> werden Querys sowie Chan Aktivit&auml;ten geloggt, wichtig für Turniere.</li>
      </ul></td>
    </tr>
</table>
<p>

<!-- ENDE Zusätzliche Konfiguration -->

<hr width="50%">

<!-- ANFANG vom ENDE -->

<h2>Schritt 4: Finish it!</h2><p>

Als Letztes geht Ihr einfach auf <i>Connect</i> zur&uuml;ck und klickt auf <i>Connect to Server</i>.<br>
Jetzt sollte euer IRC Client soweit ich und Ihr keine Fehler gemacht habt, ins innovaLAN-IRC connecten, und die vorher eingestellten Chans joinen.<p>

Das Team findet Ihr im IRC im Channel <i>#Northcon</i> und die jeweiligen Turnier Ansprechpartner noch einmal extra in den einzelnen Turnier Chans.<br>
Team Mitglieder und Turnier Orgas kennzeichnen sich im IRC durch ein <font color="#FF000"><b>@</b></font> (normaler Chan Operator) und / oder durch einen <font color="#FF000"><b>.</b></font> (IRC Operator) vor Ihrem Nickname.<br>

Als letztes w&uuml;nsche ich Euch viel Spaß auf der Party und falls es Probleme beim Einstellen Eures Clients geben sollte meldet euch bei mir (esco)
oder bei einem anderen Teammitglied.<br>

Zum Schluß noch eine &Uuml;bersicht der <i><font color="#FF000">Wichtigen!</i></font> Channels:<p>

<table border="0">
  <tr>
    <td>#Northcon / #the-summit etc.</td>
    <td>(die Main Channels)</td>
  </tr>
  <tr>
    <td>#Support</td>
    <td>(der Allgemeine <i>Support</i> Channel)</td>
  </tr>
  <tr>
    <td>#Beamer</td>
    <td>(der Channel des <i>Beamer Teams</i>)</td>
  </tr>
  <tr>
    <td>#Turnier.BF42</td>
    <td>(der <i>Battlefield 1942</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.BV</td>
    <td>(der <i>Blobby Volley</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.CCG</td>
    <td>(der <i>Command &amp; Conquer Generals</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.CS</td>
    <td>(der <i>Counter Strike</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.DOD</td>
    <td>(der <i>Day of Defeat</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.MoHAA</td>
    <td>(der <i>Medal of Honor Allied Assault</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.QW</td>
    <td>(der <i>QuakeWorld</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.Q3</td>
    <td>(der <i>Quake 3</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.TN</td>
    <td>(der <i>TetriNET</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.UT</td>
    <td>(der <i>Unreal Tournament</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.UT</td>
    <td>(der <i>Unreal Tournament 2003</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.WC3</td>
    <td>(der <i>Warcraft 3</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.RTCW</td>
    <td>(der <i>Wolfenstein Enemy Territory</i> Turnier Channel)</td>
  </tr>
  <tr>
    <td>#Turnier.FUN</td>
    <td>(der Channel für alle <i>FUN</i> Turniere)</td>
  </tr>
  <tr>
    <td>#Counselor</td>
    <td>(<b>DER</b> Channel überhaupt, esco &amp; muffl0ns zuhause)</td>
  </tr>
</table>

<!-- ENDE vom ENDE -->






