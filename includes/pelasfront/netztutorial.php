
<h1>Layer 2, Layer 3, DHCP - Ein Tutorial</h1>

<ul>
  <li><a href="#1">1. Grundlagen</a>
    <ul>
      <li><a href="#1.1">1.1 Was ist Unicast, was ist Broadcast?</a></li>
      <li><a href="#1.2">1.2 Was ist Layer2?</a></li>
      <li><a href="#1.3">1.3 Was ist Layer3?</a></li>
      <li><a href="#1.4">1.4 Was ist DHCP?</a></li>
    </ul>
  </li>
  <li><a href="#2">2. HLSW</a>
    <ul>
      <li><a href="#2.1">2.1 Was macht HLSW?</a></li>
      <li><a href="#2.2">2.2 Was macht der HLSW LAN Master?</a></li>
    </ul>
  </li>
  <li><a href="#3">3. Spieleigene Masterserver</a></li>
  <li><a href="#4">4. Das große Bild</a></li>
</ul>

<h2><a name="1">1. Grundlagen</a></h2>
<h3><a name="1.1">1.1 Was ist Unicast, was ist Broadcast?</a></h3>
<p>
Unicast ist die one-to-one Verbindung. Zwei Rechner tauschen Datenpakete nur untereinander aus. Dies ist bei Datenübertragungen der "Normalfall".
</p>
<p>
Broadcast ist die one-to-many Verbindung. Ein Rechner sendet ein Datenpaket, welches von vielen anderen Rechnern empfangen wird.
</p>
<p>
Das Zusammenspiel von Uni- und Broadcast wird fast ausnahmslos bei Spielen verwendet:
Der Client "sucht" per Broadcast im Netz nach Servern.
<br>
Wenn ein Server vorhanden ist, antwortet dieser dem Client per Unicast. Das eigentliche Spiel ist dann eine reine Unicast Verbindung.
</p>

<h3><a name="1.2">1.2 Was ist Layer2?</a></h3>
<p>
In einem reinem Layer2 Netz werden mit einem Broadcast alle anderen Rechner erreicht.
</p>

<h3><a name="1.3">1.3 Was ist Layer3?</a></h3>
<p>
In einem Layer3 Netz ist das Netz in sogenannte Subnetze unterteilt, die untereinander über einen oder mehrere Router verbunden sind. Jedes Subnetz ist dabei wieder ein Layer2 Netz.
<br>
Die Router können aber nur <a href="#1.1">Unicasts</a> weiterleiten, für die <a href="#1.1">Broadcast</a> stellen sie eine Barriere da.
</p>

<h3><a name="1.4">1.4 Was ist DHCP?</a></h3>
<p>
Mit dem "Dynamic Host Configuration Protocol", kurz <acronym title="Dynamic Host Configuration Protocol">DHCP</acronym>, kann man einem Computer automatisch beim Booten seine Netzkonfiguration zukommen lassen.
<br>
Das Ganze wird durch einen speziellen <a href="#1.1">Broadcast</a> des Computers ausgelöst, auf den ein DHCP Server mit <a href="#1.1">Unicast</a> antwortet. Wenn mehrere DHCP Server antworten, wird das erste Angebot benutzt.
</p>

<h2><a name="2">2. HLSW</a></h2>

<h3><a name="2.1">2.1 Was macht HLSW?</a></h3>
<p>
HLSW ist ein Game Browser für eine große Anzahl von Spielen. Er sucht per <a href="#1.1">Broadcast</a> nach den Servern und zeigt diese in einer Liste an. Dadurch das HLSW sehr viele Spiele kennt und danach in relativ kleinen Zeitabständen suchen kann, erzeugt das Programm sehr viele <a href="#1.1">Broadcasts</a>.
</p>

<h3><a name="2.2">2.2 Was macht der HLSW LAN Master?</a></h3>
<p>
Der LAN Master ist eine Verbesserung des HLSW Tools. Hierbei sucht nur noch dieser LAN Master nach den Servern im Netz, die HLSW "Clients" verbinden sich mit dem Master und fragen dort dann die Liste der Spiele ab.
<br>
Diese Feature kann aber erst ab der Beta 9 des HLSW Clients genutzt werden.
</p>


<h2><a name="3">3. Spieleigene Masterserver</a></h2>
<p>
Da man im Internet die Server nicht mittels <a href="#1.1">Broadcast</a> finden kann (wird dort ebenfalls nicht weitergeleitet) setzen fast alle Spiele hier auf sogenannte Masterserver. Diese Server haben einen festen Domainnamen (z.B. europe.battle.net) und werden von den Clients abgefragt, welche Spiele derzeit laufen. Eigene Server im Internet registrieren sich bei diesem Masterserver, so das sie mit auf der Liste stehen.
<br>
Bekanntester Vertreter für den LAN gebrauch ist wohl der BnetD, ein von Blizzard unabhängig entwickelter BattleNet Server. Leider ist er wie fast alle Master Server etwas schwach auf der Brust und zudem auch vom Spielehersteller verboten.
</p>

<h2><a name="4">4. Das große Bild</a></h2>
<p>
Generell kann man sagen, dass sich bis ca. 500 Teilnehmer der Einsatz von <a name="1.3">Layer3</a> noch nicht lohnt.
Die durch HLSW erzeugten <a href="#1.1">Broadcasts</a> lassen sich effektiv durch den LAN Master eindämmen.
DHCP ist nicht zu empfehlen, da die Suche nach weiteren DHCP Servern (welche falsche IPs verteilen) bei so vielen Rechnern & Teilnehmern großes Unbehagen verursacht.
</p>
<p>
Ab ca. 1000 Teilnehmern wird es mit <a name="1.2">Layer2</a> endgültig eng: Der (durch den HLSW-Master schon reduzierte) <a href="#1.1">Broadcast</a> nimmt überhand. Die Folgen: Personal Firewalls erzeugen 100% CPU-Last durch die durchgehende "Bombardierung" mit kleinen Pakten (gesehen & verifiziert auf der "The-Summit 2").
<br>
Manche Spiele droppen sporadisch alle Clients (u.a. BF1942).
</p>
<p>
Durch <a name="1.3">Layer3</a> kann man nun viel erreichen: Durch die Aufteilung in Subnetze wird der <a href="#1.1">Broadcast</a> fast auf Null reduziert, HLSW Master und ggf. Masterserver tun ihr übriges.
Da nun auch nur noch ca. 20 Rechner in einem Subnetz zu finden sind, ist der Einsatz von DHCP auch sehr einfach. Falsche DHCP Server können ihr "Unwesen" nur noch innerhalb ihres Subnetzes treiben. 
</p>
