<?php

include_once "dblib.php";
include_once "format.php";
if (LOCATION == 'internet')
  include_once "session.php";


if (empty($_GET['action'])) {
  $gamerlist = msz_gamerlist();
  
  echo "<h2>Spieler-Suche/-Gebote</h2>\n";
  
  echo "<table width='100%' cellpadding='3' cellspacing='1' border='0'>";
  echo "<tr>
        <td class='forum_titel' align=\"center\"><b>User</b></td>
        <td class='forum_titel' align=\"center\"><b>Spiel</b></td>
        <td class='forum_titel' align=\"center\"><b>Skill</b></td>
        <td class='forum_titel' align=\"center\"><b>Anzahl</b></td>
        <td class='forum_titel' align=\"center\"><b>Kommentar</b></td>
        </tr>";

  $bgClass = 'forum_bg1';
  foreach ($gamerlist as $value) {
    echo "<tr>";
		echo "  <td class='$bgClass' align=\"center\" valign=\"top\"><a href=\"benutzerdetails.php?nUserID=$value[userID]\">".db2display($value[LOGIN])."</a></td>\n";
		printf("  <td class='$bgClass' align=\"center\" valign=\"top\">%s</td>\n", (!empty($value['turnierID']) ? "<img src=\"$value[bild]\"> <a href=\"turniere.php?action=detail&turnierid=$value[turnierID]\">$value[sname]</a>" : $value['sname']));
		echo "  <td class='$bgClass' align=\"center\" valign=\"top\">$value[skill]</td>\n";
		echo "  <td class='$bgClass' align=\"center\" valign=\"top\">$value[anzahl]</td>\n";
	  $wrap_at = 20;
	  $value['kommentar'] = preg_replace('/([^\s\<\>]{'.$wrap_at.','.$wrap_at.'})/', '\1 ', $value[kommentar]);
		echo "  <td class='$bgClass' align=\"center\" width=\"250\">".db2display($value[kommentar])."</td>\n";		
	  echo "</tr>\n";
	  $bgClass = ($bgClass == 'forum_bg1') ? 'forum_bg2' : 'forum_bg1';	 
	}
  echo "</table>\n";  
  
  print_r($gamerlist);
}

function msz_gamerlist() {
  global $nPartyID;
  
  $rc = array();
  $sql = "SELECT
            `m`.`ID`, `m`.`userID`, `u`.`LOGIN`, `m`.`turnierID`, 
            COALESCE(`t`.`NAME`, `m`.`spielname`) as `sname`, 
            `m`.`clanID`, `c`.`NAME`, `m`.`skill`,             
            `m`.`kommentar`, `m`.`anzahl`,
            `t`.`BILDKL` as `bild`
          FROM
            mitspielzentrale AS m, USER AS u
          LEFT JOIN
            TURNIERLISTE AS t ON 
              t.TURNIERID = m.turnierID AND
              t.MANDANTID = m.mandantID
          LEFT JOIN
            CLAN AS c ON 
              c.CLANID = m.clanID
          WHERE
            m.userID = u.USERID AND
            `m`.`mandantID` = '$nPartyID' AND
            `m`.`type` IN ('biete gamer', 'suche gamer')";
  
  if ($res = DB::query($sql)) {
    while ($row = $res->fetch_assoc())
      array_push($rc, $row);      
  }  
  return $rc;
}

/*
select 
  `m`.`ID`, `u`.`LOGIN`, `m`.`turnierID`, COALESCE(`t`.`NAME`, `m`.`spielname`) as `sname`, `m`.`clanID`, `c`.`NAME`, `m`.`skill`, `m`.`kommentar`, `m`.`anzahl`
from 
  mitspielzentrale as m, USER as u
left join 
  TURNIERLISTE as t 
    on t.TURNIERID = m.turnierID AND 
    t.MANDANTID = m.mandantID
left join 
  CLAN as c
    on c.CLANID = m.clanID
where 
  m.userID = u.USERID
*/

function msz_gamelist() {
  $rc = array();  
  $sql = "SELECT
            `ID`, `type`, `userID`, `turnierID`, `spielname`, 
            `skill`, `kommentar`, `anzahl`, `clanID`
          FROM
            `mitspielzentrale`
          WHERE
            `type` = 'games'";
  echo $sql;
  return $rc;
}
?>
