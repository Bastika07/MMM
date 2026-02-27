<?php

// TODO CHECK IF ROW AFTER & BEFORE IS LOGE OR NOT
// TODO, what if normal group takes Clanname
// TODO db2dipslay everywhere?

include_once "dblib.php";
include_once "session.php";
include_once "constants.php";
include_once "sitzplan_generate.php";
include_once "sitzgruppenfunctions.php";

function choseAltRow($nPartyID){
  $reihe = $_GET['reihe'];
  if(checkTierForRow($nPartyID, $_GET['block'], ($reihe+1))){
    if(checkTierForRow($nPartyID, $_GET['block'], ($reihe-1))){
      echo "<p><input type='radio' name='ersatzReihe' value=".($reihe-1)."> Reihe davor 
      <input type='radio' name='ersatzReihe' value=".($reihe+1)." checked> Reihe danach</p>";
    }
    else{
       echo "<input type=\"hidden\" name=\"ersatzReihe\" value=\"".($_GET['reihe']+1)."\">\n";
       echo "<p>Als Ersatzreihe wurde f&uuml;r diese Position automatisch ".($_GET['reihe']+1)." bestimmt.
       Da dies die erste Reihe im Block ist.</p>";
    }
  }
  else{
   echo "<input type=\"hidden\" name=\"ersatzReihe\" value=\"".($_GET['reihe']-1)."\">\n";
   echo "<p>Als Ersatzreihe wurde f&uuml;r diese Position automatisch ".($_GET['reihe']-1)." bestimmt,
   da dies die letzte Reihe im Block ist.</p>";
  }
}

//------------------Function which draw, or generate HTML ------------------

// Entscheidet ob der User nur einen Butten für Sitzgruppen, oder auch einen für Clangrupen sieht
function evaluateUserChoices($nLoginID, $nPartyID){
  
  global $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE;
  
  if(USER::hatBezahlt()){
    
    echo "<h2>Unabh&auml;ngige Sitzgruppe erstellen</h2>";
    
    sitzgruppenButton($nPartyID);
    $clanID = userClanID($nLoginID, $nPartyID);
    if ($clanID){
      if (!userClangroupExists($nLoginID, $nPartyID)){  
      echo "<h2>Clangruppe erstellen</h2>";
      echo "<p>F&uuml;r Deinen Clan Existiert noch keine eigene Sitzgruppe, Du kannst nun alternativ eine
                                Clangruppe erstellen. Diese hat den Namen Deines Clans und alle Clanmember k&ouml;nnen ihr
                                beitreten, ohne dass sie eingeladen werden m&uuml;ssen.</p>
            <table class='rahmen_allg' cellspacing=\"1\" cellpadding=\"3\" border=\"0\">
            <tr><td class='TNListeTDB' width=\"25%\">
            <form name='input' method='get'>
             Name der neuen Sitzgruppe: </TD><TD class='TNListeTDA' width=\"33%\">
             <input type='hidden' name='block' value=".$_GET['block'].">
             <input type='hidden' name='gruppe'value='erstellen'>
             <input type='hidden' name='platz' value=".$_GET['platz'].">
             <input type='hidden' name='reihe' value=".$_GET['reihe'].">
             <input type='hidden' name='clan'  value=".$clanID.">
             <input type='submit' value='Clangruppe erstellen'>
             </td><td class='TNListeTDB' width=\"41%\">";
      choseAltRow($nPartyID);
      echo "</td></tr></table></FORM>";
      }     
    }
    echo "<br>\n";
  }
  else{
    PELAS::fehler('Du musst erst bezahlen, bevor du eine Sitzgruppe erstellen darfst.');
  } 
}

function sitzgruppenButton($nPartyID){
  echo "<p>Du hast den Platz: ".$_GET['platz']." in der Reihe: ".$_GET['reihe']." ausgew&auml;hlt. Du kannst nun entscheiden, 
                          wie Deine Sitzgruppe hei&szlig;en soll. Ausserdem kannst Du eine Erstatzreihe bestimmen, in die Deine Gruppe umgebrochen werden kann,
                          falls dies erforderlich wird.</P>
          <table class='rahmen_allg' cellspacing=\"1\" cellpadding=\"3\" border=\"0\">
          <tr><td class='TNListeTDB' width=\"25%\">
          <form name='input' method='get'>
           Name der neuen Sitzgruppe: </TD><TD class='TNListeTDA' width=\"33%\">
           <input type='hidden' name='block' value=".$_GET['block'].">
           <input type='hidden' name='gruppe' value='erstellen'>
           <input type='hidden' name='platz' value=".$_GET['platz'].">
           <input type='hidden' name='reihe' value=".$_GET['reihe'].">
           <input type='text' name='name'>
           <input type='submit' value='Erstellen'>
           </TD><td class='TNListeTDB' width=\"41%\">";
        choseAltRow($nPartyID);       
           echo "</td></TR>
           </table>
           </FORM>";
}

//------------------ end of Draw functions-----------------------------



// Sitzplan neu generieren für ebene xy, speichern und anzeigen zum test
//TODO && isset($nLoginID)
if(isset($_GET['reihe']) && isset($_GET['platz']) && checkTierForRow($nPartyID, $_GET['block'], $_GET['reihe'])){
  if(sitzPlatzResOffen($nPartyID)){ 
    //Checken ob Platz frei
    $sql = " select
              PLATZ
             from
              SITZ
             where
              REIHE='".$_GET['reihe']."' AND PLATZ='".$_GET['platz']."' AND MANDANTID='$nPartyID'";
    $res = DB::query($sql);
    if(mysql_num_rows($res)>0){
      Pelas::fehler('Dieser Sitzplatz ist bereits belegt');
    }
    else{
      $sql = " select
              type
             from
              sitzplan_def
             where
              REIHE='".$_GET['reihe']."' AND PLATZ='".$_GET['platz']."' AND MANDANTID='$nPartyID'";
      $res = DB::query($sql);
      $row = mysql_fetch_row($res);
      $q = "SELECT 
            STATUS
            FROM 
              ASTATUS
            WHERE
              USERID = '$nLoginID' AND
              MANDANTID = '$nPartyID' AND
              STATUS IN ($STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, 
                         $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, 
                          $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS)";
      $res = DB::query($q);
      $row2 = mysql_fetch_row($res);
      if(($row[0]=='platz'&&USER::hatBezahlt())||($row[0]=='logenplatz'&&$row2[0]==$STATUS_BEZAHLT_LOGE)){
      //Checken ob User schon in einer Sitzgruppe ist
        if(!checkUserSeatGroup($nPartyID, $nLoginID)){
          if (isset($_GET['gruppe']) && is_String($_GET['gruppe']) && $_GET['gruppe']=="erstellen"
              && isset($_GET['ersatzReihe']) && $_GET['ersatzReihe']>0){
    
              if (isset($_GET['clan']) && is_numeric($_GET['clan']) && $_GET['clan'] > 0){
                if($clanname = generateClanGroup($nLoginID, $nPartyID, $_GET['clan'], $_GET['reihe'],$_GET['platz'], $_GET['ersatzReihe'])){
                  if(isset($clanname) && $clanname!="" ){
                    ?>
                    <TABLE width="100%" cellspacing=5 cellpadding=0 border=0>
                      <TR><TD> <p>Die Sitzgruppe  <?php db2display($clanname); ?> wurde neu angelegt und du bist ihr beigetreten <p> </TD></TR>
                    </TABLE>
                    <?php
                  }
                  else{
                    PELAS::fehler('Fehler beim Anlegen des Datensatzes für die Clangruppe. Bitte versuch es nochmal. Evtl. 
                                bist du nicht berechtigt diese Aktion auszuführen. Falls dieser Fehler wiederholt
                                auftritt, wende dich bitte an den Webmaster.');
                  }
                }
                else{
                  PELAS::fehler('Die Gruppe konnte nicht angelegt werden. Evtl. existiert schon eine GRuppe mit dem gleichen Namen');
                }
              }
              elseif (isset($_GET['name']) && is_String($_GET['name']) && $_GET['name'] != ""){
  
                if (($groupName = generateGroup($nLoginID, $nPartyID, $_GET['name'], $_GET['reihe'],$_GET['platz'], $_GET['ersatzReihe']))){
                  if(isset($groupName)){
                    ?>
                    <TABLE width="100%" cellspacing=5 cellpadding=0 border=0>
                      <TR><TD><p>Die Sitzgruppe <?php $groupName?> wurde neu angelegt, und Du bist ihr beigetreten <p> </TD></TR>
                    </TABLE>
                    <?php            
                    echo "<p><p>";
                  } else {
                    PELAS::fehler('Fehler beim Anlegen des Datensatzes für die Gruppe. Bitte versuch es nochmal. Evtl. 
                                bist du nicht berechtigt diese Aktion auszuführen. Falls dieser Fehler wiederholt
                                auftritt, wende dich bitte an den Webmaster.');
                  }
                }
                else{
                  PELAS::fehler('Die Sitzgruppe konnte nicht angelegt werden, vermutlich existiert sie schon.');
                }
              }    
          } 
          else{
            evaluateUserChoices($nLoginID, $nPartyID);
          }     
        }
        else{
            //TODO Hier würde das Umsetzen gechecked und gemacht
            ?>
            <TABLE width="100%" cellspacing=5 cellpadding=0 border=0>
              <TR><TD> Du bist bereits in einer Gruppe und kannst deshalb keine neue Gruppe erstellen </TD></TR>
            </TABLE>
            <?php
        }
      }
      else{
        PELAS::fehler('Um eine Sitzgruppe zu erstellen, musst du bereits gezahlt haben. 
                        Logenpl&auml;tze d&uuml;rfen nur von Teilnehmern reserviert werden, die auch daf&uuml;r bezahlt haben.');
      }
           
    }
  } else {
    Pelas::fehler('Die Stizplatzreservierung ist geschlossen.');
  }
}



?>
<TABLE width="100%" cellspacing=5 cellpadding=0 border=0>
    <TR valign="top">
      <TD width="180" NOWRAP>
      <table width="180" class="rahmen_allg" cellspacing="1" cellpadding="2" border="0">
        <tr><td colspan="2" class="navbar">Teilnehmer</td></tr>
        <tr><td width="40" class="hblau">Platz</td><td width="110" class="hblau"><div id="platzInfo">...</div></td></tr>
        <tr><td class="dblau">Login</td><td class="dblau" id="nickInfo">...</td></tr>
      </table>
        <!--<FORM name="status"><b><i>Status:</i></b><hr size="1" noshade width="100%" class="newsline">
          <center>
            <TABLE cellspacing=5 cellpadding=0 border=0 width="100%">
            <TR><TD NOWRAP align=center><input class="sitzistatus" type="text" id="mouse_platz" style="width: 62px;" value="Platz">
            <input class="sitzistatus" type="text" style="width: 82px;" id="mouse_status" value="Status"></TD></TR>
            <TR><TD NOWRAP colspan='2' align=center> <input class="sitzistatus" type="text" style="width: 148px;" id="mouse_nick" value=""></TD></TR>              
            <TR><TD NOWRAP colspan='2'<input class="sitzistatus" type="text" style="width: 148px;" id="mouse_clan" value=""></TD></TR>
            </TABLE>
          </center>
        </FORM>-->
      </TD>
      <TD width="100%">
         <!-- Legend Tables -->
        <TABLE class="rahmen_allg" cellspacing="1" cellpadding="2" border="0" width="380">
          <tr>
            <td colspan="6" class="navbar">Legende</td>
          </tr>
          <TR>
            <TD width="15" class="dblau" valign="middle"><IMG src="sitzgfx/s_frei.gif" height="13" width="13" border="0" alt=></TD>
            <TD width="85" class="hblau" valign="middle" NOWRAP>frei</TD>
            <TD width="15" class="dblau" valign="middle"><IMG src="sitzgfx/s_self_res.gif" height="13" width="13" border="0"></TD>
            <TD width="85" class="hblau" valign="middle" NOWRAP>Dein Platz</TD>
            <TD width="15" class="dblau" valign="middle"><IMG src="sitzgfx/blocked.gif" height="13" width="13" border="0"></TD>
            <TD width="85" class="hblau" valign="middle" NOWRAP>Hindernis</TD>
            <!---
            <TD width="15" class="dblau" valign="middle"><IMG src="sitzgfx/w_h.gif" height="13" width="13" border="0"></TD>
            <TD width="85" class="hblau" valign="middle" NOWRAP>Wand</TD>
             -->
          </TR>
          <TR>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/s_vor.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>vorgemerkt</TD>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/s_clan_res.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Deine Gruppe</TD>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/st_r.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Stuhl</TD>
            <!---
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/w_b_h.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Beamer</TD>
            -->
          </TR>
          <TR>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/s_res.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>reserviert</TD>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/s_res_search.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Selektion</TD>
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/s_frei_loge.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Loge</TD>
            <!---
            <TD class="dblau" valign="middle"><IMG src="sitzgfx/w_t_h.gif" height="13" width="13" border="0"></TD>
            <TD class="hblau" valign="middle" NOWRAP>Tür</TD>
            -->
          </TR>
        </TABLE>
      </TD>
    </TR>
    <TR>
      <TD VALIGN=TOP>
      <!-- Grouplist Table -->
      <TABLE class="rahmen_allg" cellspacing=1 cellpadding=2 border=0 width="100%">
          <TR><TD class="navbar">Gruppenname</TD><TD class="navbar" align=center >Anzahl</TD></TR>
          <?php
           //TODO to many rows, shold be 8 times less this makes second statemen for count necessary
            $sql = " select
                      s.GRUPPEN_NAME, s.GRUPPEN_ID
                     FROM
                      sitzgruppe s, sitzgruppen_mitglieder m, SITZ z, sitzplan_def d
                     where
                      d.MANDANTID='$nPartyID' AND 
                      z.MANDANTID='$nPartyID' AND 
                      s.MANDANTID='$nPartyID' AND 
                      m.MANDANTID='$nPartyID' AND 
                      d.EBENE='".$_GET['block']."' AND  d.REIHE=z.REIHE AND m.USERID=z.USERID 
                      AND s.GRUPPEN_ID=m.GRUPPEN_ID GROUP BY GRUPPEN_NAME";
           $res = DB::query($sql);
           while ($row = mysql_fetch_row($res)){
              if (isset($class) && $class == "dblau") {
                $class = "hblau";
              } else {
                $class = "dblau";
              }
              $sql2 = " select
                        Count(*)
                       FROM
                        sitzgruppen_mitglieder
                       where
                        GRUPPEN_ID=$row[1]";
              $res2 = DB::query($sql2);      
              $row2 = mysql_fetch_row($res2);
              $memberCount = $row2[0];
              echo"<TR onMouseOver=\"javascript:groupOver($row[1])\" onMouseOut=\"javascript:groupOut($row[1])\"><TD class=$class><a href=sitzgruppen.php?gruppenID=$row[1] id=\"group_$row[1]\">".db2display($row[0])."</a></TD><TD class=$class align=center>$memberCount</TD></TR>";
            }                      
          ?>
      </TABLE>
    </TD>
  <TD VALIGN=TOP>
  
  <table width="380" cellspacing="0" cellpadding="0" border="0" width="380">
  <tr><td>
  
   <!-- Seatingplan  -->
<?php

//---------------TODO Hier muss der neue Sitzplan rein (in diese TD)------------------- von hier
$ebene = (int) $_GET['block'];

echo "<br>";

//GeneriereSitzplan($nPartyID, $ebene);
generateSitzplan2($nPartyID, $ebene);

echo"<p align='left' ><a href=sitzplan.php><img src='gfx/headline_pfeil.png' border='0'> Block&uuml;bersicht</a> </p>";

?>

<div id="layer2" style="position:absolute; top:160px; left:0px; width:210px; height:1px; padding:10px; visibility:hide; visibility:hidden; "></div>
<script language="JavaScript" src="<?=PELASHOST?>sitzplan.js" type="text/javascript"></script>
<script language="JavaScript">init('<?=PELASHOST?>userbild/')</script>


<script language="JavaScript">
<!--
function gores(Reihe,Platz)
{
<?php
 if (isset($bResOffen) && $bResOffen == 1) {
  if ($nLoginID >= 1) {
    if ($aStatus  == $STATUS_BEZAHLT || $aStatus  == $STATUS_BEZAHLT_LOGE) {
      ?>
  	if (document.forms.theaction.iAction[0].checked == true) {
  		tempAction = 1;
  	} else {
  		tempAction = 2;
  	}
  	clanMate = document.forms.theaction.clanmate.value;
  	
  	document.location.href="sitzplan.php?ebene=<?= $ebene ?>&reihe="+Reihe+"&tisch="+Platz+"&iAction="+tempAction+"&clanMate="+clanMate;
  <?php
    } else {
  	echo "alert(\"$str[bezahlen]\");\n";
    }
  } else {
    echo "alert(\"Nicht eingeloggt!\");\n";
  }
 } else {
   echo "alert(\"Die Sitzplatzreservierung wurde noch nicht eröffnet!\");\n";
 }
  ?>
}

//-->
</script>

<?php

//include_once PELASHOST."sitzplan_html.php?nPartyID=$nPartyID&ebene=$ebene";
//readfile (PELASDIR."sitzbild/sitzplan_html_".$nPartyID."_".$ebene.".txt");

//echo "<p align=\"center\"><img src=\"".PELASHOST."sitzbild/sitzplan_bild_".$nPartyID."_".$ebene.".png?time=".time()."\" usemap=\"#mmm_map\" border=\"0\"></p>";


//------------------ Bis hier

?>

</td></tr>
</table>

</TD></TR>
</TABLE>

<?php

function generateSitzplan2($mandantId, $ebene) {
  global $nLoginID;
  
  if (isset($nLoginID)) {
    // eigene Sitzgruppe rausfinden

// Alternativvorschlag, der die mandantID in sitzgruppen_mitglieder redundant macht
//    $sql = "SELECT
//              m.GRUPPEN_ID
//            FROM
//              sitzgruppen_mitglieder m, sitzgruppe s
//            WHERE
//              s.MANDANTID = '$mandantId' AND s.GRUPPEN_ID=m.GRUPPEN_ID AND m.USERID = '$nLoginID'";
//     $res = DB::query($sql);
//     $row = mysql_fetch_row($res);
//     $ownGroupId = $row[0];
    $sql = "SELECT
              GRUPPEN_ID
            FROM
              sitzgruppen_mitglieder
            WHERE
              MANDANTID = '$mandantId' AND
              USERID = '$nLoginID'";
     $res = DB::query($sql);
     $row = mysql_fetch_row($res);
     $ownGroupId = $row[0];
  }  
  
  $sql = "SELECT
            sd.xcord, sd.ycord, sd.reihe, sd.platz, sd.type, s.USERID, s.RESTYP, u.LOGIN, sm.GRUPPEN_ID
          FROM
            sitzplan_def sd
          LEFT JOIN
            SITZ s ON 
              sd.mandantID = s.MANDANTID AND 
              sd.reihe = s.REIHE AND 
              sd.platz = s.PLATZ
          LEFT JOIN
            USER u ON
              s.USERID = u.USERID
          LEFT JOIN
            sitzgruppen_mitglieder sm ON
              sm.USERID = s.USERID AND
              sm.MANDANTID = sd.mandantID
          WHERE 
            sd.mandantID = '$mandantId' AND
            sd.ebene = '$ebene'
          ORDER BY
            sd.ycord, sd.xcord";
  $res = DB::query($sql);
  $matrix = array();
  $userIds = array();
  while ($row = mysql_fetch_assoc($res)) {
    if (isset($row['USERID'])) {
      // Hier sitzt ein User      
      // Userid in Array für Javascript-Ausgabe
      $userIds[$row['USERID']] = $row['LOGIN'];
      // Icon bestimmen
      if (isset($nLoginID) && isset($row['GRUPPEN_ID']) && $row['USERID'] != $nLoginID && $row['GRUPPEN_ID'] == $ownGroupId) {
        $row['icon'] = 's_clan_res.gif';
        $row['iconName'] = 'imgClanRes';
      } else if ($row['type'] == 'platz') {
        if (isset($nLoginID) && $nLoginID == $row['USERID']) {
          $row['icon'] = 's_self_res.gif';
          $row['iconName'] = 'imgSelfRes';
        } else {
          $row['icon'] = 's_res.gif';
          $row['iconName'] = 'imgRes';
        }
      } else if ($row['type'] == 'logenplatz') {
        if (isset($nLoginID) && $nLoginID == $row['USERID']) {
          $row['icon'] = 's_self_res.gif';
          $row['iconName'] = 'imgSelfRes';
        } else {
          $row['icon'] = 's_res_loge.gif';
          $row['iconName'] = 'imgResLoge';
        }
      }
    } else {
      // Hier sitzt kein User, icon bestimmen
      switch ($row['type']) {
        case 'platz': $row['icon'] = 's_frei.gif'; break;
        case 'logenplatz': $row['icon'] = 's_frei_loge.gif'; break;
        case 'gang': $row['icon'] = 'gang.gif'; break;
        case 'stuhl_r': $row['icon'] = 'st_r.gif'; break;
        case 'stuhl_l': $row['icon'] = 'st_l.gif'; break;
        case 'stuhl_o': $row['icon'] = 'st_o.gif'; break;
        case 'stuhl_u': $row['icon'] = 'st_u.gif'; break;
        case 'blocked': $row['icon'] = 'blocked.gif'; break;
      }
      $row['iconName'] = '';
    }
    $matrix[$row['ycord']][$row['xcord']] = array('reihe' => $row['reihe'], 'platz' => $row['platz'], 'type' => $row['type'], 'userId' => $row['USERID'], 'restyp' => $row['RESTYP'], 'icon' => $row['icon'], 'iconName' => $row['iconName']);
  }
  
// ALternative  
//  $sql = "select
//          sm.USERID, sm.GRUPPEN_ID, s.GRUPPEN_NAME
//          from
//            sitzgruppen_mitglieder sm, sitzgruppe s
//          where
//            s.MANDANTID = '$mandantId' AND
//            sm.GRUPPEN_ID = s.GRUPPEN_ID";
//  $res = DB::query($sql);
  
  $sql = "select
            sm.USERID, sm.GRUPPEN_ID, s.GRUPPEN_NAME
          from
            sitzgruppen_mitglieder sm, sitzgruppe s
          where
            sm.MANDANTID = '$mandantId' AND
            sm.GRUPPEN_ID = s.GRUPPEN_ID";
  $res = DB::query($sql);
  $groups = array();
  while ($row = mysql_fetch_assoc($res)) {
    if (!isset($groups[$row['GRUPPEN_ID']])) {
      $groups[$row['GRUPPEN_ID']]['name'] = $row['GRUPPEN_NAME'];
      $groups[$row['GRUPPEN_ID']]['userIds'] = array();
    }
    $groups[$row['GRUPPEN_ID']]['userIds'][] = $row['USERID'];
  }
        
  ?>
  <style type="text/css">
    table.sitzplan_bg {
      padding: 0px;
      border-collapse: collapse;
    }
    
    div.group {
      width: 200px;
    }
    
  </style>  
  <script type="text/javascript">

    // preload images
    var imgBelegt = new Image(); imgBelegt.src = 'sitzgfx/belegt.gif';
    
    var imgSearch = new Image(); imgSearch.src = 'sitzgfx/s_res_search.gif';                                              
    
    var imgRes = new Image(); imgRes.src = 'sitzgfx/s_res.gif';
    var imgFrei = new Image(); imgFrei.src = 'sitzgfx/s_frei.gif';
    var imgResHigh = new Image(); imgResHigh.src = 'sitzgfx/s_res_high.gif';
    var imgFreiHigh = new Image(); imgFreiHigh.src = 'sitzgfx/s_frei_high.gif';
    
    var imgResLoge = new Image(); imgResLoge.src = 'sitzgfx/s_res_loge.gif';
    var imgFreiLoge = new Image(); imgFreiLoge.src = 'sitzgfx/s_frei_loge.gif';
    var imgResLogeHigh = new Image(); imgResLogeHigh.src = 'sitzgfx/s_res_loge_high.gif';
    var imgFreiLogeHigh = new Image(); imgFreiLogeHigh.src = 'sitzgfx/s_frei_loge_high.gif';
    
    var imgSelfRes = new Image(); imgSelfRes.src = 'sitzgfx/s_self_res.gif';
    var imgClanRes = new Image(); imgClanRes.src = 'sitzgfx/s_clan_res.gif';
    
    var imgStuhl = new Image(); imgStuhl.src = 'sitzgfx/st_r.gif';
    
    var user = new Array();
    var groups = new Array();
    var status = new Array();
    
    
    <?php
    foreach ($userIds as $key => $val) {
      echo "user[$key] = \"$val\";";
    }
    
    foreach ($groups as $groupId => $val) {
      echo "groups[$groupId] = new Array();\n";
      echo "groups[$groupId][\"name\"] = \"$val[name]\";\n";
      echo "groups[$groupId][\"user\"]= new Array();\n";
      foreach ($val['userIds'] as $userId) {
        echo "groups[$groupId][\"user\"].push($userId);\n";
      }
    }
    ?> 
        
    status[0] = "frei";
    status[1] = "vorgemerkt";
    status[2] = "reserviert";  
  </script>
  <?php
  echo "<script src=\"".PELASHOST."sitzplan2.js\" type=\"text/javascript\"></script>";
  ?>

  <table name="sitzplan" class="sitzplan_bg" cellpadding="0" cellspacing="0" align="center"><tbody>
  <?php
  foreach ($matrix as $x => $val) {
    echo "<tr>\n";
    foreach ($val as $y => $val) {
      if (!empty($val['userId'])) {
        if ($val['type'] == 'platz') {
          echo "<td><a href=\"benutzerdetails.php?nUserID=$val[userId]\"><img border=\"0\" height=\"13\" width=\"13\" src=\"sitzgfx/$val[icon]\" id=\"user_$val[userId]\" onmouseover=\"javascript:seatOver(this, imgResHigh, '$val[reihe]-$val[platz]')\" onmouseout=\"javascript:seatOut(this, $val[iconName])\"></a></td>\n";
        } else if ($val['type'] == 'logenplatz') {
          echo "<td><a href=\"benutzerdetails.php?nUserID=$val[userId]\"><img border=\"0\" height=\"13\" width=\"13\" src=\"sitzgfx/$val[icon]\" id=\"user_$val[userId]\" onmouseover=\"javascript:seatOver(this, imgResLogeHigh, '$val[reihe]-$val[platz]')\" onmouseout=\"javascript:seatOut(this, $val[iconName])\"></a></td>\n";
        }
      } else if ($val['type'] == 'platz') {
        //TODO evtl. hier auch stizresoffenCheck einfügen und link wegnehmen
        echo "<td><a href=\"?block=$ebene&reihe=$val[reihe]&platz=$val[platz]\"><img border=\"0\" height=\"13\" width=\"13\" src=\"sitzgfx/$val[icon]\" onmouseover=\"javascript:seatOver(this, imgFreiHigh, '$val[reihe]-$val[platz]')\" onmouseout=\"javascript:seatOut(this, imgFrei)\"></a></td>\n";
      } else if ($val['type'] == 'logenplatz') {
        //TODO evtl. hier auch stizresoffenCheck einfügen und link wegnehmen
        echo "<td><a href=\"?block=$ebene&reihe=$val[reihe]&platz=$val[platz]\"><img border=\"0\" height=\"13\" width=\"13\" src=\"sitzgfx/$val[icon]\" onmouseover=\"javascript:seatOver(this, imgFreiLogeHigh, '$val[reihe]-$val[platz]')\" onmouseout=\"javascript:seatOut(this, imgFreiLoge)\"></a></td>\n";
      } else {
        echo "<td><img height=\"13\" width=\"13\" src=\"sitzgfx/$val[icon]\"></td>\n";
      }
      
      unset($str);
    }
    echo "</tr>\n";
  }
  ?>
</tbody></table>

<?php
}



?>
