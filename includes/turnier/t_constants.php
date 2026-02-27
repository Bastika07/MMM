<?php
/**
 * Konstanten fuer das Turniersystem
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/16 ore - initial version
 * @package turniersystem
 * @subpackage include
 */

/* allgemeine Flags */
define('TS_SUCCESS',		0);			// erfolgreich
define('TS_ERROR',		0x80000000);		// allgeminer Fehler
define('TS_REG_CLOSED',		TS_ERROR | 1);		// Anmeldung geschlossen
define('TS_NOT_LOGGED_IN',	TS_ERROR | 2);		// User nicht eingeloggt
define('TS_NOT_PAYED',		TS_ERROR | 3);		// User nicht bezahlt
define('TS_ALREADY_REG',	TS_ERROR | 4);		// User bereits fuer dieses Turnier gemeldet (in anderem team)
define('TS_TOO_FEW_COINS',	TS_ERROR | 5);		// User hat zuwenig Coins
define('TS_TOO_MANY_TEAMS',	TS_ERROR | 6);		// Turnier hat maximale Teamanzahl erreicht
define('TS_NOT_LEADER',		TS_ERROR | 7);		// User ist nicht Teamchef
define('TS_TEAM_FULL',		TS_ERROR | 8);		// Team ist voll
define('TS_NOT_QUEUED',		TS_ERROR | 9);		// User will gar nicht joinen
define('TS_NOT_MEMBER',		TS_ERROR | 10);		// User ist nicht TeamMember
define('TS_IS_LEADER',		TS_ERROR | 11);		// User ist Teamleader (kann nicht gekickt werden)
define('TS_NO_SUCH_TEAM',	TS_ERROR | 12);		// Team nicht vorhanden (wurde geloescht)
define('TS_DUP_TEAM',		TS_ERROR | 13);		// Team mit selben Namen existiert bereits
define('TS_DUP_LEAGUE_ID',	TS_ERROR | 14);		// Ligaid ist schon in diesem Turnier vorhanden
define('TS_NOT_ADMIN',		TS_ERROR | 15);		// User ist kein Admin
define('TS_DUP_PENALTY',	TS_ERROR | 16);		// Strafe wurde bereits vergeben
define('TS_DUP_RESULT',		TS_ERROR | 17);		// Ergebnis wurde bereits eingetrage
define('TS_MATCH_NOT_READY',	TS_ERROR | 18);		// Paarung ist nicht Spielbar
define('TS_TOURNEY_NOT_RUNNING',TS_ERROR | 19);		// das turnier läuft derzeit nicht
define('TS_TOURNEY_RUNNING',	TS_ERROR | 20);		// das turnier läuft derzeit
define('TS_TEAMNAME_EMPTY',	TS_ERROR | 21);		// das turnier läuft derzeit
define('TS_RESOLVE_IDS',	TS_ERROR | 22);		// User Id's muessen bei Teamanlage eingegeben werden


/* Flags fuer Match.class */
define('T_TEAM1',	1);
define('T_TEAM2',	2);
define('T_ADMIN',	3);

define('T_FREILOS',	-1);

/* t_match flags */
define('MATCH_UNKNOWN',		0x0000);		// Status nicht bekannt
define('MATCH_READY',		0x0001);		// paarung kann gespielt werden
define('MATCH_PLAYING',		0x0002);		// paarung wird gespielt
define('MATCH_COMPLETE',	0x0004);		// paarung abgeschlossen & ergebnis vorhanden

define('MATCH_TEAM1_ACCEPT',	0x0010);		// team1 stimmt ergbnis zu
define('MATCH_TEAM2_ACCEPT',	0x0020);		// team2 stimmt ergbnis zu
define('MATCH_TEAM1_SEEDED',	0x0040);		// team1 geseeded (info fuers zulosen)
define('MATCH_TEAM2_SEEDED',	0x0080);		// team2 geseeded (info fuers zulosen)

define('MATCH_USER_RESULT',	0x0100);		// ergebnis von usern eingetragen
define('MATCH_ADMIN_RESULT',	0x0200);		// admin hat ergebnis eingetragen / veraendert
define('MATCH_RANDOM_RESULT',	0x0400);		// ergebnis wurde gewuerfelt

define('MATCH_TEAM1_GELB',	0x1000);
define('MATCH_TEAM2_GELB',	0x2000);
define('MATCH_TEAM1_ROT',	0x4000);
define('MATCH_TEAM2_ROT',	0x8000);


/* t_events flags */
define('EVENT_MSG',		0x01);			// Event ist eine Message
define('EVENT_FILE',		0x02);			// Event ist ein File (not used yet)
define('EVENT_HIDDEN',		0x10);			// Event kann nur von admins gesehen werden

/* t_team flags */
define('TEAM_USE_COINS',	0x01);			// Team verbraucht Coins fuer dieses Turnier
define('TEAM_IS_ACTIVE',	0x02);			// Team nimmt aktiv am Turnier teil (nicht ausgeschieden)


/* t_team2user flags */
define('TEAM2USER_QUEUED',	0x001);			// user will aufgenommen werden
define('TEAM2USER_MEMBER',	0x002);			// user ist mitglied
define('TEAM2USER_LEADER',	0x004);			// user ist leader
define('TEAM2USER_LIGAMAIL',	0x010);			// user akzeptiert mail an liga
define('TEAM2USER_NEW',		0x100);			// internes dirty flag: user erstellt
define('TEAM2USER_MODIFY',	0x200);			// internes dirty flag: user geaendert
define('TEAM2USER_DELETE',	0x400);			// internes dirty flag: user geloescht


// t_turnier flags
define('TURNIER_AB18', 			0x01);		// Turnier wird nicht in der Internet Turnierlist angezeigt
define('TURNIER_COVERAGE',		0x02);		// Turnier wird in die Internet-coverage einbezogen
define('TURNIER_SINGLE',		0x10);		// Single Elimination Turnier
define('TURNIER_DOUBLE',		0x20);		// Double Elimination Turnier
define('TURNIER_MULTIRUNDEN',		0x30);		// 
define('TURNIER_RUNDEN',		0x40);		// Reines Rundenturnier (Wie beim Fußball)
define('TURNIER_HTML',		0x80); // HTML-Only-Turnier
define('TURNIER_AUTOANMELDESTART',	0x100);		// automatischer start der anmeldung
define('TURNIER_TREE_RUNDEN',		0x200);		// Turnierbaum mit vorrunden - SE oder DE wird über zusätzliches FLAG bestimmt

// t_turnier status
define('TURNIER_STAT_RES_NOT_OPEN',	0x00);		// Anmeldung noch nicht geoeffnet
define('TURNIER_STAT_RES_OPEN',		0x01);		// Anmeldung geoeffnet
define('TURNIER_STAT_RES_CLOSED',	0x02);		// Anmeldung geschlossen
define('TURNIER_STAT_SEEDING',		0x03);		// Turnier wird geseeded
define('TURNIER_STAT_RUNNING',		0x04);		// Turnier läuft
define('TURNIER_STAT_PAUSED',		0x05);		// Turnier unterbrochen
define('TURNIER_STAT_FINISHED',		0x06);		// Turnier beendet
define('TURNIER_STAT_CANCELED',		0x07);		// Turnier abgesagt


// t_turnier liga
define('TURNIER_LIGA_NORMAL',	0);
define('TURNIER_LIGA_FUN',	1);
define('TURNIER_LIGA_WWCL',	2);
define('TURNIER_LIGA_NGL',	3);


// anzeigeflags fuer statuswechsel
define('TURNIER_CMD_OPEN',		0x0001);
define('TURNIER_CMD_CLOSE',		0x0002);
define('TURNIER_CMD_SEED',		0x0004);
define('TURNIER_CMD_PLAY',		0x0008);
define('TURNIER_CMD_PAUSE',		0x0010);
define('TURNIER_CMD_CANCEL',		0x0020);
define('TURNIER_CMD_TRANSFER',		0x0040);		//Vorrunden in die Hauptturniere transferieren
define('TURNIER_CMD_PRELIM_UP',		0x0080);		//Vorrunden aktualisieren ( Spieler ohne Seeding hinzufuegen )
define('TURNIER_CMD_NOT_OPEN',		0x0100);
define('TURNIER_CMD_PRELIM_DEL',	0x0200);		//Vorrunden loeschen um neu zu seeden
define('TURNIER_CMD_FINISHED',		0x0400);		//Turnier auf beendet setzten
define('TURNIER_CMD_RESEED', 		0x0800);		//erneut seeden
define('TURNIER_CMD_RESET', 		0x1000);		//Turnier zuruecksetzten
?>
