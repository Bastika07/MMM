<?php
include_once "dblib.php";
include_once "session.php";

if (!isset($debug)) { $debug = "";}

function sitzPlatzResOffen($nPartyID){
  $sql = "select
          STRINGWERT
         from
          CONFIG
         where
          MANDANTID = '$nPartyID' AND PARAMETER='SITZPLATZRES_OFFEN'";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  if ($row[0] == 'J') {
    return true;
  } else {
    return false;
  }  
}


function getRowLength($nPartyID, $row){
  $sql = "select
            count(PLATZ)
           from
            sitzplan_def
           where
            MANDANTID = '$nPartyID' AND REIHE = '$row'";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  return $row[0];
}

// Returns an Array $freeSeats of all free seats if there are any, otherwise returns false.
function freeSeats($nPartyID, $reihe){
  $sql = "select
            PLATZ
           from
            SITZ
           where
            MANDANTID = '$nPartyID' AND REIHE = '$reihe' ORDER BY PLATZ";
  $res = DB::query($sql);
  $length = getRowLength($nPartyID, $reihe);
  if ($res->num_rows < $length){
    $freeSeats = array();
    $row = $res->fetch_row();
    for ($i=1; $i<=$length; $i++) {
      if($row[0]==$i){
        $row = $res->fetch_row();
      }
      else{
        $freeSeats[] = $i;
      }
    }
    return $freeSeats;
  }
  else {
    return FALSE;
  }
}

function checkTierForRow($nPartyID, $ebene, $reihe){
  $sql = "select
            EBENE
          from
            sitzplan_def
          where
            MANDANTID='$nPartyID' AND REIHE='$reihe'";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  if($row[0] == $ebene){
    return TRUE;
  }
  else{
    return FALSE;
  }
}

// Checks if First subjects alternative row is free - and Moves it to the first place in this row (moving all other too) - or FALSE
function moveFirst($nPartyID, $reihe){

  $sql = " select
            g.UMBRECHENNACH, g.REIHE
           FROM
            SITZ s, sitzgruppen_mitglieder m, sitzgruppe g
           WHERE
            s.REIHE='$reihe' AND
            s.PLATZ='1' AND
            s.MANDANTID='$nPartyID' AND
            g.MANDANTID='$nPartyID' AND
            m.MANDANTID='$nPartyID' AND
            s.USERID=m.USERID AND
            m.GRUPPEN_ID=g.GRUPPEN_ID";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  //Check if User sits already in his altRow and chose the complementary
  if($reihe == $row[1]){
    $moveToRow =$row[0];
  }
  else{
    $moveToRow =$row[1];
  }
  $freeSeats = freeSeats($nPartyID, $moveToRow);
  if ($freeSeats){
    $length = getRowLength($nPartyID, $moveToRow);
    $actSeat = 1;
    while($actSeat<=$length){
      if(in_array($actSeat,$freeSeats)){        
        $sql = "UPDATE
                  SITZ
                SET
                  REIHE='$moveToRow'
                WHERE
                  MANDANTID='$nPartyID' AND REIHE='$reihe' AND PLATZ=1";
        if(movePeopleRow($nPartyID, $moveToRow, 1, $actSeat)){
          $debug.= $sql;
          DB::query($sql);
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
      $actSeat++;
    }
  } else {
    return FALSE;
  }
}

// Checks if Last subjects alternative row is free - and Moves it to the last place in this row (moving all other too) - or FALSE
function moveLast($nPartyID, $reihe, $length){

  $sql = " select
            g.UMBRECHENNACH, g.REIHE
           FROM
            SITZ s, sitzgruppen_mitglieder m, sitzgruppe g
           WHERE
            s.REIHE='$reihe' AND
            s.PLATZ='$length' AND
            s.MANDANTID='$nPartyID' AND
            g.MANDANTID='$nPartyID' AND
            m.MANDANTID='$nPartyID' AND
            s.USERID=m.USERID AND
            m.GRUPPEN_ID=g.GRUPPEN_ID";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  if($reihe == $row[1]){
    $moveToRow =$row[0];
  }
  else{
    $moveToRow =$row[1];
  }
  $freeSeats = freeSeats($nPartyID, $moveToRow);
  if ($freeSeats){
    $length = getRowLength($nPartyID, $moveToRow);
    $actSeat = $length;
    while($actSeat>0){
      if(in_array($actSeat,$freeSeats)){
        $sql = "UPDATE
                  SITZ
                SET
                  REIHE='$moveToRow'
                WHERE
                  MANDANTID='$nPartyID' AND REIHE='$reihe' AND PLATZ='$length'";
        if(movePeopleRow($nPartyID, $moveToRow, $length, $actSeat)){
           $debug.= $sql;
           DB::query($sql);
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
      $actSeat--;
    }
  } else {
    return FALSE;
  }
}

function placeUser($nPartyID, $userID, $gruppenID){
  //TODO Restyp hardcoded should be chaged later
  $restyp = '1';

  //TODO not so nice, if less in altrow, but are selected first
  $sql = " select
            REIHE
           from
            SITZ s, sitzgruppen_mitglieder m
           where
            s.MANDANTID='$nPartyID' and
            m.MANDANTID='$nPartyID' and
            s.USERID=m.USERID AND
            m.GRUPPEN_ID='$gruppenID'";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  
  $sql2 = " select
            REIHE, UMBRECHENNACH
           from
            sitzgruppe
           where
            MANDANTID='$nPartyID' and GRUPPEN_ID='$gruppenID'";
  $res2 = DB::query($sql2);
  $row2 = $res2->fetch_row();
  
  if($row[0]==$row2[0]){
    $reihe = $row2[0];
    $altreihe = $row2[1];
  }
  else{
    $reihe = $row2[1];
    $altreihe = $row2[0];
  }
 
  $sqlRMax = " select
                MAX(s.PLATZ)
               from
                SITZ s, sitzgruppen_mitglieder m
               where
                m.GRUPPEN_ID = '$gruppenID' AND
                m.USERID=s.USERID AND
                s.MANDANTID='$nPartyID' AND
                m.MANDANTID='$nPartyID' AND
                s.REIHE='$reihe'";
                
  $sqlRMIN = " select
                MIN(s.PLATZ)
               from
                SITZ s, sitzgruppen_mitglieder m
               where
                m.GRUPPEN_ID = '$gruppenID' AND
                m.USERID=s.USERID AND
                s.MANDANTID='$nPartyID' AND
                m.MANDANTID='$nPartyID' AND
                s.REIHE='$reihe'";
               
  $resRMax = DB::query($sqlRMax);
  $resRmin = DB::query($sqlRMIN);
  
  $reiheMaxPlatzRow = $resRMax->fetch_row();
  $reiheMaxPlatz    = $reiheMaxPlatzRow[0];
  $reiheMinPlatzRow = $resRmin->fetch_row();
  $reiheMinPlatz    = $reiheMinPlatzRow[0];
  $length = getRowLength($nPartyID, $reihe);
  $debug.= "MaxMinLen".$reiheMaxPlatz."-".$reiheMinPlatz."-".$length."<br>";
  //Checking first row
  if ($freeSeats = freeSeats($nPartyID, $reihe) ){
      $debug.= "Freeseats<br>";
    //More Space on beginning
    if (($length-$reiheMaxPlatz)>($reiheMinPlatz-1)){
      $actSeat = $reiheMaxPlatz+1;
      while($actSeat<=$length){
        if(in_array($actSeat,$freeSeats)){
          $debug.= "TryMove<br>";         
          if(movePeopleRow($nPartyID, $reihe, $reiheMaxPlatz+1, $actSeat)){
            $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP)
                      values 
                    ('$nPartyID', '$reihe', '$reiheMaxPlatz'+1, '$userID', '$restyp')";      
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        $actSeat++;
      }
      $actSeat = $reiheMinPlatz-1;
      while($actSeat>0){
        if(in_array($actSeat,$freeSeats)){
          if(movePeopleRow($nPartyID, $reihe, $reiheMinPlatz-1, $actSeat)){
            $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                      values 
                    ('$nPartyID', '$reihe', '$reiheMinPlatz'-1, '$userID', '$restyp')";     
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        $actSeat--;    
      }  
    //More Space on end
    } else {
      $actSeat = $reiheMinPlatz-1;
      while($actSeat>0){
        if(in_array($actSeat,$freeSeats)){
          if(movePeopleRow($nPartyID, $reihe, $reiheMinPlatz-1, $actSeat)){
            $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                      values 
                    ('$nPartyID', '$reihe', '$reiheMinPlatz'-1, '$userID', '$restyp')";     
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        $actSeat--;
      }
      $actSeat = $reiheMaxPlatz+1;
      while($actSeat<=$length){
        if(in_array($actSeat,$freeSeats)){
          $debug.= "TryMove<br>";         
          if(movePeopleRow($nPartyID, $reihe, $reiheMaxPlatz+1, $actSeat)){
            $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP)
                      values 
                    ('$nPartyID', '$reihe', '$reiheMaxPlatz'+1, '$userID', '$restyp')";      
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        $actSeat++;
      }
    }
  }
  //CHecking alternative row
  elseif ($freeSeats = freeSeats($nPartyID, $altreihe)){
      $debug.= "altFreeseats<br>";
    //Group sits nearer to beginning
    if (($length-$reiheMaxPlatz)>($reiheMinPlatz-1)){
      //Try to move group over beginning
      if(moveFirst($nPartyID, $reihe)){
        if(movePeopleRow($nPartyID, $reihe, $reiheMaxPlatz,'1')){
          $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                      values 
                    ('$nPartyID', '$reihe', '$reiheMaxPlatz', '$userID', '$restyp')";      
          DB::query($sql);
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
      else{
      //Try to move group over end
        if (moveLast($nPartyID, $reihe, $length)){
          if(movePeopleRow($nPartyID, $reihe, $reiheMinPlatz,$length)){
            $sql = "INSERT INTO 
                        `SITZ`
                      (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                        values 
                      ('$nPartyID', '$reihe', '$reiheMinPlatz', '$userID', '$restyp')";      
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        else{
          return FALSE;
        }
      }
    }
    //Group sits nearer to end
    else{
      //Try to move group over end
      if (moveLast($nPartyID, $reihe,$length)){
        if(movePeopleRow($nPartyID, $reihe, $reiheMinPlatz,$length)){
          $sql = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                      values 
                    ('$nPartyID', '$reihe', '$reiheMinPlatz', '$userID', '$restyp')";      
          DB::query($sql);
          return TRUE;
        }
        else{
          return FALSE;
        }        
      }
      else{
        if(moveFirst($nPartyID, $reihe)){
          if(movePeopleRow($nPartyID, $reihe, $reiheMaxPlatz,'1')){
            $sql = "INSERT INTO 
                        `SITZ`
                      (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                        values 
                      ('$nPartyID', '$reihe', '$reiheMaxPlatz', '$userID', '$restyp')";      
            DB::query($sql);
            return TRUE;
          }
          else{
            return FALSE;
          }
        }
        else {
          return FALSE;
        }
      }
    }
  }
  else {
    return FALSE;
  }
}

//THis function is not secured by TRansactions or locks, it will damage consistency of the Tables if does not finish comletely
function movePeopleRow($nPartyID, $rowToFree, $seatToFree, $moveToSeat){
  if($seatToFree<$moveToSeat){
    while($seatToFree<$moveToSeat){
      $sql = "UPDATE
                SITZ
              SET
                PLATZ='$moveToSeat'
              WHERE
                MANDANTID='$nPartyID' AND REIHE='$rowToFree' AND PLATZ='".($moveToSeat-1)."'";
      $debug.= $sql;
      DB::query($sql);
      $moveToSeat--;
    }
    return TRUE;
  }
  elseif($seatToFree>$moveToSeat){
    while($seatToFree>$moveToSeat){
      $sql = "UPDATE
                SITZ
              SET
                PLATZ='$moveToSeat'
              WHERE
                MANDANTID='$nPartyID' AND REIHE='$rowToFree' AND PLATZ='".($moveToSeat+1)."'";
      $debug.= $sql;
      DB::query($sql);
      $moveToSeat++;
    }
    return TRUE;
  }
  elseif($seatToFree==$moveToSeat){
    return TRUE;
  }
  else{
   return FALSE;
  }
}

// returns the $gruppenID if a group for that player already exists. Otherwise returns FALSE.
function checkUserSeatgroup ($nPartyID, $userID){
  
  $sql = "select 
            `GRUPPEN_ID`
          from 
            `sitzgruppen_mitglieder`
           WHERE
            USERID='$userID' AND 
            MANDANTID='$nPartyID'";
  $res = DB::query($sql);
  
  //Teilnehmer ist bereits in einer Sitzgruppe eingetragen
  $row = $res->fetch_row();
  
  if ($row){
    //Nun stehen die Optionen Austreten, oder Umsetzen bereit.
    return $row[0];
    
  }
  //Teilnehmer ist NICHT in einer Sitzgruppe eingetragen
  else {
     return false;
  }
}

//Returns the users ClanID if he is in a Clan, otherwise returns false
function userClanID($userID, $nPartyID){
  $sql = "select 
          `CLANID`
        from 
          `USER_CLAN`
         WHERE
          USERID='$userID' AND MANDANTID='$nPartyID'"
          ;
  $res = DB::query($sql);
  $row = $res->fetch_row();
  if ( $row){
    return $row[0];
  }
  else {
    return FALSE;
  }
}
//Returns the users clangroup if one exists, otherwise returns false
function userClangroupExists($userID, $nPartyID){
  $clanID = userClanID($userID, $nPartyID);
  if ($clanID){
    $sql = "select 
      `GRUPPEN_ID`
        from 
         `sitzgruppe`
        WHERE
         CLANID='$clanID' AND MANDANTID='$nPartyID'";
    if ($res = DB::query($sql)){
      $row = $res->fetch_row();
     return $row[0];
    }
    else {
      return FALSE;
    }
  }
  else {
    return FALSE;
  }
}

function groupExists($groupName, $nPartyID){
  $sql = "select 
      `GRUPPEN_ID`
        from 
         `sitzgruppe`
        WHERE
         GRUPPEN_NAME='$groupName' AND MANDANTID='$nPartyID'";
  if ($res = DB::query($sql)){
    if ($row = $res->fetch_row()){
      return TRUE;
    } else {
      return FALSE;
    }
  }
  else {
    return FALSE;
  }
}

function generateClanGroup($userID, $nPartyID, $clanID, $reihe, $platz, $ersatzreihe){
  //Has to be differentiated later on.
  $restyp = '1';
  if (!checkUserSeatgroup($nPartyID, $userID)){
    if (!userClangroupExists($userID, $nPartyID)){
      $sql = "select 
              u.CLANID, c.NAME
            from 
              USER_CLAN u, CLAN c
            WHERE
              u.USERID='$userID' AND u.MANDANTID='$nPartyID' AND u.CLANID='$clanID' AND u.CLANID=c.CLANID";
      $res = DB::query($sql);
      $row = $res->fetch_row();
      if ($row){
        $clanname = $row[1];
        $sql1 = "INSERT INTO 
                  `sitzgruppe`
                (MANDANTID, GRUPPEN_NAME, CLANID, ERSTELLT_VON, WANNANGELEGT, REIHE, UMBRECHENNACH) 
                   values 
                ('$nPartyID', '$clanname', '$clanID', '$userID', UNIX_TIMESTAMP(), '$reihe', '$ersatzreihe')";
        if (!DB::query($sql1)) {
           return FALSE;
        }
        else {
          $gruppenID = DB::$link->insert_id;

          $sql2 = "INSERT INTO 
                    `sitzgruppen_mitglieder`
                  (USERID, MANDANTID, GRUPPEN_ID) 
                    values ('$userID', '$nPartyID', '$gruppenID')";
          if (!DB::query($sql2)) {
            $sqldelete = "DELETE 
                            from 
                          `sitzgruppe`
                          WHERE
                           MANDANTID='$nPartyID' AND GRUPPEN_ID='$gruppenID' 
                          AND ERSTELLT_VON='$userID' AND CLANID='$clanID'";
            DB::query($sqldelete) ;
            return FALSE;
          } else {
            $sql3 = "INSERT INTO 
                      `SITZ`
                    (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                      values 
                    ('$nPartyID', '$reihe', '$platz', '$userID', '$restyp')";                    
            if (!DB::query($sql3)) {
              $sqldelete1 = "DELETE 
                            from 
                          `sitzgruppe`
                          WHERE
                           MANDANTID='$nPartyID' AND GRUPPEN_ID='$gruppenID' 
                          AND ERSTELLT_VON='$userID' AND CLANID='$clanID'";
              DB::query($sqldelete1) ;
              
              $sqldelete2 = "DELETE 
                            from 
                          `sitzgruppen_mitglieder`
                          WHERE
                           MANDANTID = '$nPartyID' AND USERID = '$userID' AND GRUPPEN_ID='$gruppenID'" ;
              DB::query($sqldelete2) ;
               return FALSE;
            } else {
              return $clanname;
            }
          }
        }
      }
      else {
        return FALSE;
      }
    }
    else {
      //Dies ist sehr hdslich, eigentlich sollte die URL geklvscht werden, selbes Problem wie 
      //beim alten Sitzplan, mit REfreshs
      return FALSE;
    }
  }
  else {
    return FALSE;
  }
}

//returns the True if succesfull,  otherwise its false.
function generateGroup($userID, $nPartyID, $groupName, $reihe, $platz, $ersatzreihe){
  
  $restyp = '1';
  if (!checkUserSeatgroup($nPartyID, $userID)){
    if (groupExists($groupName, $nPartyID)){
       return FALSE;
    }
    else {
      $sql1 = "INSERT INTO 
                `sitzgruppe`
              (MANDANTID, GRUPPEN_NAME, ERSTELLT_VON, WANNANGELEGT, REIHE, UMBRECHENNACH) 
                 values 
              ('$nPartyID', '$groupName', '$userID', UNIX_TIMESTAMP(), '$reihe', '$ersatzreihe')";
      if (!DB::query($sql1)) {
         return FALSE;
      }
      else {
      
        $gruppenID = DB::$link->insert_id;
        $sql2 = "INSERT INTO 
                  `sitzgruppen_mitglieder`
                (USERID, MANDANTID, GRUPPEN_ID) 
                  values ('$userID', '$nPartyID', '$gruppenID')";
                  
        if (!DB::query($sql2)) {
          $sqldelete = "DELETE 
                          from 
                        `sitzgruppe`
                        WHERE
                         MANDANTID='$nPartyID' AND GRUPPEN_NAME='$groupName' 
                         AND ERSTELLT_VON='$userID'";
          DB::query($sqldelete);
          return FALSE;
        } else {
          $sql3 = "INSERT INTO 
                    `SITZ`
                  (MANDANTID, REIHE, PLATZ, USERID, RESTYP) 
                    values 
                  ('$nPartyID', '$reihe', 
                    '$platz', '$userID', '$restyp')";
          if (!DB::query($sql3)) {
            $sqldelete1 = "DELETE 
                          from 
                        `sitzgruppe`
                        WHERE
                         MANDANTID='$nPartyID' AND GRUPPEN_NAME='$groupName' 
                        AND ERSTELLT_VON='$userID' AND CLANID='$clanID'";
            DB::query($sqldelete1) ;
            
            $sqldelete2 = "DELETE 
                          from 
                        `sitzgruppen_mitglieder`
                        WHERE
                         MANDANTID = '$nPartyID' AND GRUPPEN_ID = '$gruppenID' 
                        AND USERID = '$userID'";
            DB::query($sqldelete2) ;
            return FALSE;
          } else {
            return $groupName;
          }
        }
      }
    }
  }
  else {
    return FALSE;
  }
}

function hasInvitation($userID, $gruppenID, $nPartyID){
  $sql = "select
            GRUPPEN_ID
          from
            sitzgruppen_einladung
          WHERE
            USERID='$userID' AND GRUPPEN_ID='$gruppenID' AND MANDANTID='$nPartyID'";
  $res = DB::query($sql);
  if ($res->num_rows>0){
    return TRUE;
  }
  else {
    return FALSE;
  }
}

function rejectInvitation($userID, $gruppenID, $nPartyID){
  if (hasInvitation($userID, $gruppenID, $nPartyID)){
    $sql = "delete
              from
            sitzgruppen_einladung
              WHERE
            USERID='$userID' AND GRUPPEN_ID='$gruppenID'AND MANDANTID='$nPartyID'";
    $res = DB::query($sql);
    if (DB::$link->affected_rows>0){
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  else {
    return FALSE;
  }
}

function joinGroup($nPartyID, $userID,  $gruppenID){
  if (!checkUserSeatgroup($nPartyID, $userID)){
    if (hasInvitation($userID, $gruppenID, $nPartyID)){
      if (placeUser($nPartyID, $userID, $gruppenID)){
        $sql = "delete
                  from 
                sitzgruppen_einladung
                WHERE
                  USERID='$userID' AND GRUPPEN_ID='$gruppenID' AND MANDANTID='$nPartyID'";
        if (DB::query($sql)){  
          $sql2 = "INSERT INTO 
                    `sitzgruppen_mitglieder`
                  (USERID, MANDANTID, GRUPPEN_ID) 
                    values ('$userID', '$nPartyID', '$gruppenID')";
          if (DB::query($sql2)){
            return TRUE;
          }
          else {
            return FALSE;
          }
        }
        else {
          return FALSE;
        }
      }
      else {
        return FALSE;
      }      
    }
    else {
      return FALSE;
    }
  }
  else {
    return FALSE;
  }
}

function joinClanGroup($userID, $nPartyID, $clanID){
  if (!checkUserSeatgroup($nPartyID, $userID)){
  $sql = "select 
              u.CLANID, s.GRUPPEN_ID
            from 
              USER_CLAN u, sitzgruppe s
            WHERE
              u.USERID='$userID' AND u.MANDANTID='$nPartyID' AND u.CLANID='$clanID' AND u.CLANID=s.CLANID";
    if ($res = DB::query($sql)){
      $row = $res->fetch_assoc();
      $gruppenID = $row['GRUPPEN_ID'];
      if (placeUser($nPartyID, $userID, $gruppenID)){

        $sql = "INSERT INTO 
                  `sitzgruppen_mitglieder`
                (USERID, MANDANTID, GRUPPEN_ID) 
                  values ('$userID', '$nPartyID', '$gruppenID')";
        if (DB::query($sql)){
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
      else {
        return FALSE;
      }      
    }
    else {
      return FALSE;
    }
  }    
  else {
    return FALSE;
  } 
}



function deleteAndRegroup($userID, $gruppenID, $nPartyID){
  $debug.="Hello1<br>";   
  // Geting necessery variables
  // Get place and Row which will be deleted
  $sql = "select
            PLATZ, REIHE 
          from
            SITZ
          WHERE
            USERID='$userID' AND MANDANTID='$nPartyID'";
  $res = DB::query($sql);
  $row = $res->fetch_row();
  $deletedSeat = $row[0];
  $deletedInRow = $row[1];
  $debug.="Row-Seat: ".$deletedInRow."-".$deletedSeat." <br>";
  
  
  $sql2 = "select
            REIHE, UMBRECHENNACH
          from
            sitzgruppe
          WHERE
            GRUPPEN_ID='$gruppenID' AND MANDANTID='$nPartyID'";
  $res2 = DB::query($sql2);
  $row2 = $res2->fetch_row();  
  $altrow = -1;
  if ($deletedInRow == $row2[0]){
    $altrow = $row2[1];
  }
  else {
    $altrow = $row2[0];
  }
  $debug.="Altrow: ".$altrow." <br>";
  
  //Get Max Min Place in the row
  $sql3 = "select
            MIN(s.PLATZ), MAX(s.PLATZ)
          from
            SITZ s, sitzgruppen_mitglieder m
          WHERE
            s.MANDANTID='$nPartyID' AND
            m.MANDANTID='$nPartyID' AND
            s.REIHE='$deletedInRow' AND
            m.GRUPPEN_ID='$gruppenID' AND
            m.USERID=s.USERID";
  $res3 = DB::query($sql3);
  $deletedRowMinSeat = -1;
  $deletedRowMaxSeat = -1;
  if($row3 = $res3->fetch_row()){
    if($row3[0]){
      $deletedRowMinSeat = $row3[0];
    }
    if($row[1]){
      $deletedRowMaxSeat = $row3[1];
    }
  }
  $debug.="Min-Max in Row: ".$deletedRowMinSeat."-".$deletedRowMaxSeat."<br>";
  
  //Get Max Min Place in the altrow
  $sql4 = "select
            MIN(s.PLATZ), MAX(s.PLATZ)
          from
            SITZ s, sitzgruppen_mitglieder m
          WHERE
            s.MANDANTID='$nPartyID' AND
            m.MANDANTID='$nPartyID' AND
            s.REIHE='$altrow' AND
            m.GRUPPEN_ID='$gruppenID' AND
            m.USERID=s.USERID";
  $res4 = DB::query($sql4);
  $altrowMin = -1;
  $altrowMax = -1;
  if($row4 = $res4->fetch_row()){
    if($row4[0]){
      $altrowMin = $row4[0];
    }
    if($row4[1]){
      $altrowMax = $row4[1];
    }
  }
  $debug.="Min-Max in AltRow: ".$altrowMin."-".$altrowMax."<br>";
  
  // Start of Porcessing
  //if this row brakes over the end, the first in the row will be set on the deleted users place
  $rowLength = getRowLength($nPartyID,$deletedInRow);
  if( $deletedRowMaxSeat == $rowLength && $altrowMax == $rowLength 
      && $deletedRowMinSeat != $rowLength){
    $debug.="Hello2<br>";   
    $sql = "DELETE from
              SITZ
            WHERE
              USERID='$userID' AND MANDANTID='$nPartyID'";
    DB::query($sql);
    if (DB::$link->affected_rows != -1){
      $sql = "UPDATE
                SITZ
              SET
                PLATZ = '$deletedSeat'                
              where
                MANDANTID='$nPartyID' AND REIHE='$deletedInRow' AND PLATZ='$deletedRowMinSeat'";
      DB::query($sql);
      if (DB::$link->affected_rows != -1){      
        return TRUE;           
      }
      else{
        return FALSE;
      }                 
    }
    else{
      return FALSE;
    }       
  }
  //if this row brakes over the beginning, the last in the row will be set on the deleted users place
  elseif( $deletedRowMinSeat == 1 && $altrowMin == 1 && $deletedRowMaxSeat != 1){
   $debug.="Hello3<br>";   
    $sql = "DELETE from
              SITZ
            WHERE
              USERID='$userID' AND MANDANTID='$nPartyID'";
    DB::query($sql);
    if (DB::$link->affected_rows != -1){
      $sql = "UPDATE
                SITZ
              SET
                PLATZ = '$deletedSeat'                
              where
                MANDANTID='$nPartyID' AND REIHE='$deletedInRow' AND PLATZ='$deletedRowMaxSeat'";
      DB::query($sql);
      if (DB::$link->affected_rows != -1){      
        return TRUE;           
      }
      else{
        return FALSE;
      }                 
    }
    else{
      return FALSE;
    }    
  }  
  //if deleted user was first or last, and altrow has no groupmembers in it
  elseif($deletedSeat==$deletedRowMinSeat || $deletedSeat==$deletedRowMaxSeat){
    $debug.="Hello4<br>";   
    $sql = "DELETE from
              SITZ
            WHERE
              USERID='$userID' AND MANDANTID='$nPartyID'";
    DB::query($sql);
    if (DB::$link->affected_rows != -1){      
      return TRUE;           
    }
    else{
      return FALSE;
    }    
  }
  //user was between first and last of the row, aand altrow has no groupmembers in it
  else{
    $debug.="Hello5<br>";   
    $sql = "DELETE from
              SITZ
            WHERE
              USERID='$userID' AND MANDANTID='$nPartyID'";
    DB::query($sql);
    if (DB::$link->affected_rows != -1){
      $debug.="Hello6<br>";   
      $sql = "UPDATE
                SITZ
              SET
                PLATZ = '$deletedSeat'                
              where
                MANDANTID='$nPartyID' AND REIHE='$deletedInRow' AND PLATZ='$deletedRowMinSeat'";
      DB::query($sql);      
      if (DB::$link->affected_rows != -1){        
        $debug.="Hello7<br>";   
        return TRUE;           
      }
      else{
        return FALSE;
      }            
    }
    else{
      return FALSE;
    }       
  }
}



function leaveGroup($userID, $nPartyID){
  if (checkUserSeatgroup($nPartyID, $userID)){     
    $sql = "Select GRUPPEN_ID from
              sitzgruppen_mitglieder
            WHERE
              USERID='$userID' AND MANDANTID='$nPartyID'";
    if (!$res = DB::query($sql)){
      return FALSE;
    }
    else {
      $row = $res->fetch_row();
      $gruppenID = -1;
      $gruppenID = $row[0];
      if (!$gruppenID>0){
        return FALSE;
      }
      else {
        if(deleteAndRegroup($userID, $gruppenID, $nPartyID)){ 
          $sql2 = "DELETE from
                  sitzgruppen_mitglieder
                WHERE
                  USERID='$userID' AND MANDANTID='$nPartyID'";
          DB::query($sql2);
          if( DB::$link->affected_rows == 1){       
            $sql3 = "Select * from
                  sitzgruppen_mitglieder
                WHERE
                  GRUPPEN_ID='$gruppenID' AND MANDANTID='$nPartyID'";
            $res = DB::query($sql3);
            if (!$res->num_rows>0){
              // Hier m|sste bei einem Fehler ein Rollback stattfinden
              
//      Alternative        
//               $sql4 = "DELETE from
//                  sitzgruppe
//                WHERE
//                  GRUPPEN_ID='$gruppenID'";             
              
              $sql4 = "DELETE from
                  sitzgruppe
                WHERE
                  GRUPPEN_ID='$gruppenID' AND MANDANTID='$nPartyID'";
              DB::query($sql4);
              return TRUE;
            }
            else {
              return TRUE;
            }
          }
          else {
            return FALSE;
          }
        }
        else {
          return FALSE;
        }          
      }
    }
  }
  else {
    return FALSE;
  }
}

?>