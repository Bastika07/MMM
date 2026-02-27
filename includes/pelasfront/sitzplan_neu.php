<?php
include_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "constants.php";
include_once "sitzgruppenfunctions.php";


// Functions



function showUsersForInvitation() {
  global $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, 
                $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, 
                $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $nPartyID;
  echo "<script type=\"text/javascript\">\n";
  echo "<!--\n";
  echo "var user = new Array();\n";
  $q = "SELECT 
          a.USERID, u.LOGIN 
        FROM 
          ASTATUS as a, USER as u 
        where 
          a.status IN ( $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, 
                $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, 
                $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS) AND 
        a.USERID = u.USERID and
        a.MANDANTID='$nPartyID'";
  $res = DB::query($q);
  while ($row = mysql_fetch_assoc($res)) {
    echo "user[".$row['USERID']."] = \"".$row['LOGIN']."\";\n";
  }
  echo "//-->\n";
  echo "</script>\n";
  
  echo "<form method=\"get\" name=\"formular\" action=\"/sitzgruppen.php\">\n";
  echo "<input type=\"text\" size=\"12\" name=\"nick\" value=\"\" onKeyup=\"printAuswahl()\">\n";
  echo "<select name=\"einladen\">\n";
  echo "<script type=\"text/javascript\">\n";
  echo "<!--\n";
  echo "function printAuswahl() {\n";
  echo "  var i, j, addme;\n";
  echo "  inp = document.formular.nick.value; \n";
  echo "  inp = inp.replace(/([\\[\\]\\\\\\/\\*\\.\\?\\(\\)\\-\\<\\>\\{\\}\\|\\^\\$\\+\\&])/g, \"\\\\$1\");\n";
  echo "  var search = eval(\"/\" + inp + \"/i\");\n";
  
  echo "  document.formular.einladen.length = 0;\n";
  
  echo "  if (inp.length > 0) {\n";
  echo "  j = 0;\n";
  echo "  for (var id in user) {\n";
  echo "    if (search.test(user[id])) {\n";
  echo "      addme = new Option(user[id], id);\n";
  echo "      document.formular.einladen[j] = addme;\n";
  echo "      j++;\n";
  echo "    }\n";
  echo "  }\n";
  echo "  document.formular.einladen.length = j;\n";
  echo "  }\n";
  echo "}\n";
  echo "//-->\n";
  echo "</script>\n";
  echo "</select>\n";
  echo "<input type=\"submit\" value=\"Einladen\">\n";
  echo "</form>\n";
}


function showGroupinvitations($nLoginID, $nPartyID){
  $sql = "select 
          s.`GRUPPEN_NAME`, s.`GRUPPEN_ID`
        from 
          sitzgruppe s, sitzgruppen_einladung e
         WHERE
          e.USERID='$nLoginID' AND e.GRUPPEN_ID=s.GRUPPEN_ID AND s.MANDANTID='$nPartyID' AND  e.MANDANTID='$nPartyID'";
  $res = mysql_query($sql);
  if(mysql_num_rows($res)>0){
    echo "<p>Nachfolgend siehst Du alle Dir vorliegenden Einladungen. Bitte beachte, dass Du 
          nur dann eine annehmen kannst, wenn du in noch keiner Gruppe bist.</p>";
    echo "<TABLE class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"2\"><TR width='30%'><TD class=\"navbar\">Gruppenname:</TD><TD width='60%'colspan='2' class=\"navbar\">Entscheidung</TD></TR>";       
    while($row = mysql_fetch_row($res)){
      if ($class == "hblau") {
        $class = "dblau";
      } else {
        $class = "hblau";
      }
      echo"<TR><TD $class width=\"70%\">".db2display($row[0])."</TD><TD $class width='30%'><a href=sitzplan.php?gruppe=join&einladung=$row[1]>Annehmen /</TD>
            <TD $class width='20%'><a href=sitzplan.php?gruppe=ablehnen&einladung=$row[1]>Ablehnen</TD>";
    }
     echo "</TABLE>";
  }
}

function showGroupMember($myID, $gruppenID, $nPartyID){
  $sql = " select
            u.LOGIN, s.REIHE, s.PLATZ, u.USERID
           from
            sitzgruppen_mitglieder m, USER u, SITZ s
           where
            s.MANDANTID='$nPartyID' AND m.GRUPPEN_ID='$gruppenID' AND u.USERID=m.USERID
            AND s.USERID=m.USERID
            ORDER BY REIHE,PLATZ";
  $res = DB::query($sql);
  echo "<TABLE class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"2\"><TR width='30%'><TD class=\"TNListe\">Gruppenmitglied</TD><TD class=\"TNListe\">Reihe</TD><TD class=\"TNListe\">Platz</TD><TD  class=\"TNListe\">Tauschen</TD></TR>";       
  while ($row = mysql_fetch_row($res)) {
    if ($class == "TNListeTDB") {
      $class = "TNListeTDA";
    } else {
      $class = "TNListeTDB";
    }
    if ($row[3]!=$myID){
      echo "<TR><TD class=$class>".db2display($row[0])."</TD><TD class=$class>".db2display($row[1])."</TD><TD class=$class>".db2display($row[2])."</TD><TD class=$class align=center ><a href=sitzgruppen.php?tauschen=".db2display($row[3]).">X</a></TD></TR>";
    } else {
      echo "<TR><TD class=$class>".db2display($row[0])."</TD><TD class=$class>".db2display($row[1])."</TD><TD class=$class>".db2display($row[2])."</TD><TD class=$class align=center >you</TD></TR>";
    }
  }
  echo "</TABLE>";          
}


// Main function to draw the group options, this one uses all others, direct and indirect
//Statt der Nachfolgenden Messageausgabe, nachher eine statemap, die am ende Ausgewertet wird.
function doSeatGroupColumn($nLoginID, $nPartyID){
  //Sitzplatzres Offen?
  $sitzResOffen = sitzPlatzResOffen($nPartyID);
  //Paramteranyalyse der URL, mit entsprechenden Konsequenzen
  if (isset($_GET['gruppe'])){
    if($sitzResOffen){
      if ($_GET['gruppe']=='join'){
        if(isset($_GET['clan'])){
          if(!is_numeric($_GET['clan']) || $_GET['clan'] < 0)
            PELAS::fehler('Fehler ClanID wurde falsch übergeben.');
          else {
            if (joinClanGroup($nLoginID, $nPartyID, $_GET['clan'])){
              echo "<p>Du bist der ClanGruppe beigetreten<p>";
            }
            else{
              PELAS::fehler('Fehler bei der Überprüfung der Daten. Du bist evtl. nicht berechtig der
                             Gruppe beizutreten, oder es ist nicht mehr genügend Platz in der Umgebung der Gruppe vorhanden');
            }
          }
        }
        if(isset($_GET['einladung'])){
          if(!is_numeric($_GET['einladung']) || $_GET['einladung'] < 0){
            PELAS::fehler('Fehler EinladungsID wurde falsch übergeben.');
          }
          else {
            if (!checkUserSeatgroup ($nPartyID, $nLoginID)){
              if (joinGroup($nPartyID, $nLoginID, $_GET['einladung'])){
                echo "<p>Du bist der Sitzgruppen beigetreten</p>";
                echo "<p><a href=sitzplan.php>Zurück</a></p>";
              }
              else{
                PELAS::fehler('Es ist leider nicht mehr genügend Platz vorhanden um dich in der Gruppe unterzubringen. Du kannst aber
                               eine individuelle Gruppe in der nähe erstellen, wenn du noch Platz findest.');
              }
            } else {
              PELAS::fehler('Du befindest dich bereits in einer Sitzgruppe. 
                              Du musst zuerst die Sitzgruppe verlassen, um einer anderen beizutreten.');
            }
          }
        }
      }
      elseif ($_GET['gruppe']=='verlassen'){
        if(leaveGroup($nLoginID, $nPartyID)){
          echo "<p>Du hast Deine Sitzgruppe verlassen.<p>";
        }
        else {
          PELAS::fehler('Fehler beim entfernen aus der Gruppe.');
        }
      }
      elseif ($_GET['gruppe']=='ablehnen'){
        if(isset($_GET['einladung']) && is_numeric($_GET['einladung']) && $_GET['einladung'] > 0){
          if(!rejectInvitation($nLoginID, $_GET['einladung'], $nPartyID)){
            PELAS::fehler('Fehler beim entfernen aus der Gruppe.');
          }
          else {
            echo "<p>Du hast die Einladung abgelehnt</p>";
            echo "<p><a href=sitzplan.php>Zurück</a></p>";
          }
        }
        else {
          PELAS::fehler('Fehler beim übertragen der Daten.');
        }
      }
    } else {
      Pelas::fehler('Die GruppenOptionen sind nicht verfügbar, da die Sitzplatzreservierung geschlossen wurde');
    }
  }

  // Nach evtl. Modifikationen durch die URL Paramter check des Statuses
  $gruppenID = -1;
  $gruppenID = checkUserSeatgroup ($nPartyID, $nLoginID);
  
  if ($gruppenID == -1){
    PELAS::fehler('Fehler beim Auslesen der Daten');
  }
  elseif ($gruppenID){
    $sql = "select
              GRUPPEN_NAME
            from
              sitzgruppe
            where 
              GRUPPEN_ID='$gruppenID'";
    $res = DB::query($sql);
    $row = mysql_fetch_row($res);
             
    echo "<p> Du bist in der Gruppe <a href=sitzgruppen.php?gruppenID=$gruppenID>".db2display($row[0])."</a>. ";
    if($sitzResOffen){
      echo "Um weitere
                Teilnehmer in Deine Gruppe einzuladen, gebe bitte drei Buchstaben des Logins ein und
                w&auml;hle dann aus der Klappbox. Hinweis: Du kannst nur Teilnehmer einladen, die bezahlt haben.
                <br>";      
      showUsersForInvitation();
      echo "</p><p>";
      //Anzeige der Groupmember exclusive man selbst. dadurch können Plätze getauscht werden.                
      showGroupMember($nLoginID, $gruppenID, $nPartyID);
      echo "</p>";
      showGroupinvitations($nLoginID, $nPartyID);
      echo "<p><a href='sitzplan.php?gruppe=verlassen'><img src='gfx/headline_pfeil.png' border='0'> Sitzgruppe verlassen</a></p>";

    }
  }
  else {
    if($sitzResOffen){
      echo"<p> Du bist moment in keiner Gruppe Vertreten, um eine eigene Gruppe zu erstellen, 
      klicke einfach auf einen Block, und dort auf eine freiend Platz.</p>";
      $clanID = userClanID($nLoginID, $nPartyID);
      if ($clanID){
        $gruppenID = userClangroupExists($nLoginID, $nPartyID);
        if ($gruppenID){
          echo "<p> Dein Clan hat bereits ein Gruppe gegründet, wenn du willst kannst du Deiner Clangruppe Beitreten
                 Clan-Gruppe beitreten <br>
                 <a href='sitzplan.php?gruppe=join&clan=$clanID'>Clan-Gruppe beitreten</a></p>";
        }
      }
      showGroupinvitations($nLoginID, $nPartyID);
    } else {
      Pelas::fehler('Du bist im moment in keiner Sitzgruppe, und die Sitzplatzreservierung ist nicht freigeschaltet.
       Wenn du trotzdem einen Sitzplatz haben möchtest, wende Dich bitte an einen Administrator.');
    }
  }
  if($sitzResOffen && $gruppenID==false){
    echo"<p> Bitte beachte, dass Du einer Gruppe nur dann beitreten kannst, wenn das System in der Umgebung 
              noch Platz f&uuml;r Dich schaffen kann. Ansonsten solltest du eine individuelle Gruppe gr&uuml;nden, und Dich
              so nah wie m&ouml;glich an Deine Freunde setzen.</p>";
  }
  echo"<p><a href=sitzgruppen.php><img src='gfx/headline_pfeil.png' border='0'> Alle Sitzgruppen als Liste</a> <br> <a href=teilnehmer.php><img src='gfx/headline_pfeil.png' border='0'> Teilnehmerliste</a></p>";
}




////////////----------------- Start

// Start of Processing !!!!!!!!!!!!!!!!!
if ($nPartyID < 1) {
	echo "<p>Die Session ist verlorengegangen, oder ausgeschaltet. NO nPartyID</p>";
	exit;
}

  

      echo "<!-- SitzgruppenTabelle unten-rechts start -->";
      echo "
      <TABLE class=\"rahmen_allg\" cellspacing=1 cellpadding=2 border=0 width='100%'>
    		<TR><TD class='TNListe'>Sitzgruppenoptionen</TD></TR>    
        <TR valign=top><TD class='TNListeTDA'>";
        if (isset($nLoginID) && !$nLoginID < 1) {
          doSeatGroupColumn($nLoginID, $nPartyID);
        } else{
          echo "<p> Die Sitzgruppenoptionen stehen dir erst zur Verfügung, wenn du eingelogged bist.<p>";
        }
        echo "</td></tr>
      </TABLE>";
?>
