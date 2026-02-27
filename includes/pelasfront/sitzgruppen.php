<?php
include_once "dblib.php";
include_once "format.php";
include_once "constants.php";
include "language.inc.php";
include_once "sitzgruppenfunctions.php";




if ($AnzahlProSeite < 20 || $AnzahlProSeite > 100) {
	$AnzahlProSeite = 30;
}


//suchen angewaehlt?
if ($limitListe != "") {
	$limitString = " and s.GRUPPEN_NAME like '%$limitListe%'";
} else {
	$limitString = "";
}

function ShowGruppenBlaettern()
{
	global $iSortierung, $sAddQuery, $limitListe, $str, $Anzahl_Gruppen, $AnzahlProSeite, $AktSeite;
	echo "<tr><td colspan=\"5\" align=\"right\" class=\"TNListe\">";
	$counter=0;
	for ($i=0;$i<= (ceil($Anzahl_Gruppen/$AnzahlProSeite)-1);$i++) {
		$counter++;
		if ($i!= 0) { echo "&nbsp;|&nbsp;"; }
		echo "<a href=\"sitzgruppen.php?AktSeite=".$i."&limitListe=$limitListe$sAddQuery&iSortierung=$iSortierung\" class=\"TNLink\">";
		if ($i==$AktSeite) { echo "<b>"; }
		echo ($i*$AnzahlProSeite+1)."-".($i*$AnzahlProSeite+$AnzahlProSeite)."</a>";
		if ($i==$AktSeite) { echo "</b>"; }
		if ($counter>=6) {
			$counter=0;
			echo "<br>";
		}
	}
	echo "&nbsp;|&nbsp;<a href=\"sitzgruppen.php?AktSeite=-1&limitListe=$limitListe$sAddQuery&iSortierung=$iSortierung\" class=\"TNLink\">";
	if ($AktSeite== -1) { echo "<b>"; }
	echo "$str[alle]</a> ";
	if ($AktSeite== -1) { echo "</b>"; }
	echo "</td></tr>";
}


//SitzplatzRes Offen?
$sitzResOffen = sitzPLatzResOffen($nPartyID);

// Anmeldung offen?
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'VORANMELDUNG_OFFEN' and MANDANTID = $nPartyID")->fetch_array();
// checken, ob get-variable on
if ($voranmeldung == "true" && $row['STRINGWERT'] == "J") {
	$bVoranmeld = 1;
	$sAddQuery="&voranmeldung=true";
} else {
	$bVoranmeld = 0;
	$sAddQuery="";
}


$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'ANMELDUNG_OFFEN' and MANDANTID = $nPartyID")->fetch_array();
if ($row['STRINGWERT'] == "J") {
	$bAnmeld = 1;
} else {
	$bAnmeld = 0;
}

// ######################################
// go?
if ($bAnmeld == 1 || $bVoranmeld == 1) {
  //TODO am besten das nachfolgende if und else als eigen functions darstellen, dami es später besser debigged werden kann
  if (isset($_GET['tauschen']) && $_GET['tauschen']>0 && $mygroup = checkUserSeatgroup($nPartyID, $nLoginID)){
    if($sitzResOffen){
      $sql = " select
                USERID
               from
                sitzgruppen_mitglieder
               where
                GRUPPEN_ID = ? AND USERID = ?";
      $res = DB::query($sql, $mygroup, (int)$_GET['tauschen']);
      if ($row = $res->fetch_row()) {
        
        //Mein Reihe Platz kriegen
        $sql = " select 
                  REIHE, PLATZ
                 from 
                   SITZ
                 where
                  MANDANTID=$nPartyID AND USERID=$nLoginID";
        $res = DB::query($sql);
        $row = $res->fetch_row();
        $meinPlatz = $row[1];
        $meineReihe = $row[0];
        
        //Update mich auf seinen Platz      
        $sql = " UPDATE 
                  SITZ
                 SET USERID=?
                 where
                  MANDANTID=? AND USERID=?";
        DB::query($sql, (int)$nLoginID, (int)$nPartyID, (int)$_GET['tauschen']);
        if(DB::$link->affected_rows<1){
          Pelas::fehler('Fehler beim setzen des 1. Platzes.');
        }
        
        // Ihn auf meinen alten PLatz
        $sql = " UPDATE 
            SITZ
           SET USERID=?
           where
            MANDANTID=? AND REIHE=? AND PLATZ=?";
        DB::query($sql, (int)$_GET['tauschen'], (int)$nPartyID, $meineReihe, $meinPlatz);
        if(DB::$link->affected_rows<1){
          Pelas::fehler('Fehler beim setzen des 2. Platzes');
        }
        echo "Das Umsetzen wurde durchgeführt.<br>";
        echo "<a href=sitzplan.php>zurück<a>";
        
      } else {
        Pelas::fehler('Beim umsetzen ist ein Fehler eingetreten');
      }
    } else {
      Pelas::fehler('Die Sitzplatzreservierung ist geschlossen, daher können keine Lpätze mehr getauscht werden');
    }
  } 
  elseif(isset($_GET['gruppenID']) && $_GET['gruppenID']>0){
    $sql = "select
              s.GRUPPEN_NAME, u.LOGIN, s.UMBRECHENNACH
            from
              sitzgruppe s, USER u
            where
              s.MANDANTID=? AND s.GRUPPEN_ID=? AND s.ERSTELLT_VON=u.USERID";
    $res = DB::query($sql, (int)$nPartyID, (int)$_GET['gruppenID']);
    $row = $res->fetch_row();
    if(!$row){
      PELAS::fehler('Fehler beider Verarbeitung aufgetreten');
    } else {   
      //Zoom auf Ebene
      $sqle = " select
                EBENE
               from 
                sitzplan_def
               where
                MANDANTID='$nPartyID' AND REIHE = '$row_platz[REIHE]'";
      $rese = DB::query($sqle);
      $ebene = -1;
      if($rowe = $rese->fetch_row()){
        $ebene = $rowe[0];
      }
      if( $ebene == -1){
        Pelas::fehler('Fehler beim feststellen des Blocks');
      }
      echo"
        <TABLE width=350 cellspacing=1 cellpadding=2 class='rahmen_allg'>
          <TR><TD colspan='4' class='TNListe'>Sitzgruppe </TD></TR>
          <TR class='TNListeTDB'>
            <TD class='TNListeTDB' width=25%>Name</TD>
            <TD class='TNListeTDA' colspan='3'>$row[0]</TD>
          </TR>
          <TR class='TNListeTDB'>
            <TD class='TNListeTDB' width=25%>Ersteller</TD>
            <TD class='TNListeTDA' colspan='3'>$row[1]</TD>
          </TR>
          <TR class='TNListeTDB'>
            <TD class='TNListeTDB' width=25%>Block</TD>
            <TD class='TNListeTDA'><a href=sitzplan.php?block=$ebene>$ebene</a></TD>
             <TD class='TNListeTDB' width=25%>Ersatzreihe</TD>
            <TD class='TNListeTDA'>$row[2]</TD>
          </TR>
        </TABLE>";
        
      $sql2 = "select
                s.USERID, u.LOGIN
              from
                sitzgruppen_mitglieder s, USER u
              where
                s.MANDANTID=? AND s.USERID=u.USERID AND s.GRUPPEN_ID=?
              ORDER BY
                u.LOGIN";
      $res2 = DB::query($sql2, (int)$nPartyID, (int)$_GET['gruppenID']);
      
      echo"<br>";
      
      // Mitgliedertabelle
      echo "<TABLE class='rahmen_allg' width=400 cellspacing=1 cellpadding=2 class=msg2>";  
      echo "<TR><TD class='TNListe'>Gruppenmitglieder</TD><TD class='TNListe'>Sitzreihe</TD><TD class='TNListe'>Sitzplatz</TD></TR>";
      while($row2 = $res2->fetch_array()){
        if ($class == "TNListeTDA") {
          $class = "TNListeTDB";
        } else {
          $class = "TNListeTDA";
        }
        $sql= "select
                REIHE, PLATZ
               from
                SITZ
               where
                MANDANTID='$nPartyID' AND USERID='$row2[USERID]'";
        $res = DB::query($sql);
        $row = $res->fetch_row();
        echo "<TR><TD class='$class'><a href='/benutzerdetails.php?nUserID=". db2display($row2['USERID']) ."'>". db2display($row2['LOGIN']) ."</a></TD><TD class='$class'>".db2display($row[0])."</TD><TD class='$class'>".db2display($row[1])."</TD></TR>";
      }
      echo "</TABLE>";
      echo"<br>";
      
      //Einladungstabelle
      $sql= "select
          i.USERID, u.LOGIN
         from
          sitzgruppen_einladung i, USER u
         where
          i.GRUPPEN_ID=? AND u.USERID=i.USERID";
      $res = DB::query($sql, (int)$_GET['gruppenID']);
      if ($res->num_rows>0){
        if (checkUserSeatgroup($nPartyID, $nLoginID)==(int)$_GET['gruppenID']){
          echo "<TABLE class='rahmen_allg' width=400 cellspacing=1 cellpadding=2 class=msg2>";  
          echo "<TR><TD class='TNListe'>Eingeladene Spieler</TD><TD align=center class='TNListe'>Ausladen</TD></TR>";
          while($row = $res->fetch_array()){
            if ($class == "TNListeTDA") {
              $class = "TNListeTDB";
            } else {
              $class = "TNListeTDA";
            }
            echo "<TR><TD class='$class' width=75%><a href='/benutzerdetails.php?nUserID=". db2display($row[0]) ."'>". db2display($row[1]) ."</a></TD><TD class='$class' align=center><a href=sitzgruppen.php?ausladen=".db2display($row[0]).">X<a></TR>";
          }
          echo "</TABLE>";
        } else {
          echo "<TABLE width=100% cellspacing=1 cellpadding=2 class=msg2>";  
          echo "<TR><TD class='rahmen_msgtitle'>Eingeladene Spieler: </TD></TR>";
          while($row = $res->fetch_array()){
            if ($class == "TNListeTDA") {
              $class = "TNListeTDB";
            } else {
              $class = "TNListeTDA";
            }
            echo "<TR  class='$class'><TD><a href='/benutzerdetails.php?nUserID=". db2display($row[0]) ."'>". db2display($row[1]) ."</a></TD></TR>";
          }
          echo "</TABLE>";
        }
      }
      echo"<p align='left' ><a href=sitzplan.php><img src='gfx/headline_pfeil.png' border='0'> Block&uuml;bersicht</a> </p>";
    }
  }
  elseif (isset($_GET['einladen']) && $_GET['einladen']>0){
    if($sitzResOffen){
    	$mygroup = checkUserSeatgroup($nPartyID, $nLoginID);
    	$sql = " select
              d.type
             from
              sitzplan_def d, SITZ s
             where
              d.MANDANTID='$nPartyID' AND s.MANDANTID='$nPartyID' AND
               s.USERID='$nLoginID' AND s.REIHE=d.REIHE AND s.PLATZ=d.PLATZ";
      $res = DB::query($sql);
      $row = $res->fetch_row();
      $q = "SELECT 
            STATUS
            FROM 
              ASTATUS
            WHERE
              USERID = ? AND
              MANDANTID = ? AND
              STATUS IN ($STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, 
                         $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, 
                          $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS)";
      $res = DB::query($q, (int)$_GET['einladen'], (int)$nPartyID);
      $row2 = $res->fetch_row();
    	if($mygroup && !checkUserSeatgroup($nPartyID, (int)$_GET['einladen']) && 
    	    (($row[0]=='platz'&&USER::hatBezahlt((int)$_GET['einladen']))||($row[0]=='logenplatz'&&$row2[0]==$STATUS_BEZAHLT_LOGE))){
    		$sql = "select
    							USERID
    						from
    							`sitzgruppen_einladung`
    						where
    							USERID=? AND GRUPPEN_ID=?";
    		$res = DB::query($sql, (int)$_GET['einladen'], $mygroup);
    		if($res->fetch_row()){
    			PELAS::fehler('Dieser Teilnehmer wurde bereits in die Gruppe eingeladen');
    		}else {
  	  		$sql = "insert
  	  							into
  	  						sitzgruppen_einladung (GRUPPEN_ID, USERID, MANDANTID)
  	  							values
  	  						(?, ?, ?)";
  	  		if(!DB::query($sql, $mygroup, (int)$_GET['einladen'], (int)$nPartyID)){
  	  			PELAS::fehler('Es gab einen fehler beim erstellen der Einladung, bitte versuche es erneut');
  	  		}
  	  		?>
  	      <TABLE width="100%" cellspacing=5 cellpadding=0 border=0>
            <TR><TD><p>Du hast den Teilnehmer erfolgreich in Deine Gruppe eingeladen<p> </TD></TR>
            <TR><TD><a href=sitzplan.php><img src='gfx/headline_pfeil.png' border='0'> Sitzplan</> <br> <a href=teilnehmer.php><img src='gfx/headline_pfeil.png' border='0'> Teilnehmerliste</a></TD></TR>
          </TABLE>
          <?php
    		}
    	}else {
    		PELAS::fehler('<p>Ein Fehler trat auf dies könnte folgende möglichkeiten haben</p>
    										<ul>
    										<li>Der User den Du einladen willst ist schon in einer Gruppe</li>
    										<li>Du bist in keiner Gruppe, und kannst deshalb auch niemanden Einladen</li>
    										<li>Der User den Du einladen willst hat noch nicht bezahlt</li>
    										<li>Du hast versucht ihn in eine Gruppe mit Logenplätzen einzuladen, aber er hat nicht für Loge bezahlt</li>
    										</ul>');
    		echo "<p><img src='gfx/headline_pfeil.png' border='0'> <a href=\"/sitzplan.php\">Zur&uuml;ck</p>";
  	  }
  	}else {
    	 Pelas::fehler('Die Sitzplatzreservierung ist geschlossen, daher kann niemand mehr in die Gruppe eingeladen werden');
    } 
  }
  elseif (isset($_GET['ausladen']) && $_GET['ausladen']>0){
    if ($sitzResOffen){
      $mygroup = checkUserSeatgroup($nPartyID, $nLoginID);
      $sql = "select
                USERID
              from
                sitzgruppen_einladung
              WHERE
                GRUPPEN_ID=? AND USERID=?";
      $res = DB::query($sql, $mygroup, (int)$_GET['ausladen']);
      if ($res->num_rows==1){
        $sql = "delete
                from
                  sitzgruppen_einladung
                WHERE
                  GRUPPEN_ID=? AND USERID=?";   
        $res = DB::query($sql, $mygroup, (int)$_GET['ausladen']);
        if(DB::$link->affected_rows==1){
          echo"<p> Der Benutzer wurde erfolgreich aus der Einladungsliste entfernt.</p> 
                  <p><img src=\"gfx/headline_pfeil.png\"> <a href=sitzgruppen.php?gruppenID=$mygroup> Zurück. </a></p>";
        }
        else{
        Pelas::fehler('Fehler beim ausladen aus der Gruppe. Das Löschen aus der DB schlug fehl.');
        }              
      }
      else{
        Pelas::fehler('Fehler beim ausladen aus der Gruppe. Evtl. hast Du nicht die berechtigung den User
                        aus dieser Gruppe auszuladen');
      }    
    }
  }
  //Gruppenliste wird angezeigt
  else{
    //Vorarbeit fuers Blaettern
    if ($AktSeite == "") {
    	$AktSeite = 0;
    }
    
    $sql = "select 
              count(*) 
            from 
              sitzgruppe s
            where 
              s.MANDANTID = $nPartyID
            $limitString";
    $result= DB::query($sql);
    //echo DB::$link->errno.": ".DB::$link->error."<BR>";
    $row = $result->fetch_array();
    $Anzahl_Gruppen = $row[0];
    if ($Anzahl_Gruppen == 0) {
    	$Anzahl_Gruppen = 1;
    }
    ?>
    <p>     
    	<?php 
    	echo "<p>Es gibt ".$Anzahl_Gruppen;
    	if($Anzahl_Gruppen==1){
    	  echo " Gruppe.</p>";
    	}
    	else{
    	 echo " Gruppen.</p>";
    	}
    	;?> 
    	<form method="get" action="sitzgruppen.php">
    	<input type="text" name="limitListe" size="20" maxlength="20" value="<?=$limitListe?>"> <input type="submit" value="Suchen">
    	</form>
    </p>
       
    <table class="rahmen_allg" cellpadding='1' cellspacing='1' border='0' width="400">
    <?php    
    	ShowGruppenBlaettern();    
    ?>
    <tr>
     	<td class="TNListe" width="20"><img src="lgif.gif" border="0" height="1" width="14"></td>
     	<td class="TNListe" width="250"><a href="sitzgruppen.php?AktSeite=<?=$AktSeite?>&limitListe=<?=$limitListe?><?=$sAddQuery?>&iSortierung=gruppe" class="TNLink">Gruppenname</a></td>
      <td class="TNListe" width="100">Gruppenmitglieder</td>
     	<td class="TNListe" width="30">Block</td></tr>
    </tr>
    <?php    
    // Sortierung
    if ($iSortierung == "gruppe") {
      $sAddSort = "order by s.GRUPPEN_NAME";
    } else {
    		$sAddSort = "";
    }
		$AktSeite = intval($AktSeite);
    if ($AktSeite == "-1") {
    		$sAddWhere = "";
    } else {
    		$sAddWhere = "limit ".($AktSeite*$AnzahlProSeite).",".$AnzahlProSeite;
    }    	
    $sql = "select 
    	          GRUPPEN_ID, GRUPPEN_NAME
    	        from 
    	          sitzgruppe s
    	        where 
                s.MANDANTID = $nPartyID
      	        $limitString 
      	        $sAddSort $sAddWhere";
    $result= DB::query($sql);    	
    //echo DB::$link->errno.": ".DB::$link->error."<BR>";    	
    while ($row = $result->fetch_row()) {
      $tempGName = $row[1];
    		if (strlen($tempGName) > 23 ) {
    			$Anzeige_GName = db2display(substr( $tempGName, 0, 23)."...");
    		} else {
    			$Anzeige_GName = db2display($tempGName);
    		}   		
      $sql2 = "select 
                count(*) 
              from 
                sitzgruppen_mitglieder
              where 
                GRUPPEN_ID = '$row[0]'";
      $result2= DB::query($sql2);
      //echo DB::$link->errno.": ".DB::$link->error."<BR>";
      $row2 = $result2->fetch_row();
      $anzahl_Mitglieder = $row2[0]; 		
    		
  		$sql3 = "select 
  		            d.EBENE
    	          from 
    	            sitzgruppen_mitglieder s, SITZ t, sitzplan_def d
    	          where 
                  s.GRUPPEN_ID = '$row[0]'AND s.USERID = t.USERID AND d.MANDANTID = $nPartyID AND 
                  t.REIHE=d.REIHE AND t.PLATZ=d.PLATZ";
      if($res = DB::query($sql3)){
    		  $row3 = $res->fetch_row();
    		  $block = $row3[0];
    		}
    		else{
    		  $block = "";
    		}
    		echo "<TR><TD class='TNListeTDA' align=\"center\"><a href=\"sitzgruppen.php?gruppenID=$row[0]\"><img src=\"gfx/userinfo.gif\" border=\"0\"></a></TD>";
    		echo "<TD class='TNListeTDB'>$Anzeige_GName</TD>";
    		echo "<td class='TNListeTDB'>$anzahl_Mitglieder</td>";
    		echo "<td class='TNListeTDB'>$block</td></TR>\n";
    }    
    ShowGruppenBlaettern();
    echo "</table><br>";
  }
} else {
	echo "<p>Die Anmeldung wurde noch nicht er&ouml;ffnet.</p>";
}

?>