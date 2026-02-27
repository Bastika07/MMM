<?

/*

t0xirc, the aEGiS PHP to Eggdrop gateway class
==============================================

Copyright (C) 2001-2003 Vincent Negrier aka. sIX <six@aegis-corp.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2, or (at your option)
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

*/

define("T0XIRC_VERSION", "1.0.3");

define("TCB_TIMER_DAY", "timer_day");
define("TCB_TIMER_HOUR", "timer_hour");
define("TCB_TIMER_MINUTE", "timer_minute");
define("TCB_TIMER_SECOND", "timer_second");
define("TCB_TIMER_LOOP", "timer_loop");
define("TCB_PUBMSG", "pubmsg");
define("TCB_PUBACT", "pubact");
define("TCB_PRIVMSG", "privmsg");
define("TCB_PRIVACT", "privact");
define("TCB_SELF_PUBMSG", "selfpubmsg");
define("TCB_JOIN", "join");
define("TCB_PART", "part");
define("TCB_KICK", "kick");
define("TCB_QUIT", "quit");
define("TCB_NICK_CHANGE", "nickch");

define("EREG_NICK", "[]\[a-zA-Z0-9{|}`_-]{1,9}");
define("EREG_PUBMSG", "^\[[0-9]{2}:[0-9]{2}\] <([^<>]+)> (.*)");
define("EREG_PUBACT", "^\[[0-9]{2}:[0-9]{2}\] Action: (".EREG_NICK.") (.*)");
define("EREG_PRIVMSG", "^\[[0-9]{2}:[0-9]{2}\] \[(.+)!.+\] (.*)");
define("EREG_PRIVACT_1", "^\[[0-9]{2}:[0-9]{2}\] Action to ");
define("EREG_PRIVACT_2", ": (".EREG_NICK.") (.*)");
define("EREG_SELF_PUBMSG_1", "^\[[0-9]{2}:[0-9]{2}\] #(.+)# \(#");
define("EREG_SELF_PUBMSG_2", "\) say (.*)");
define("EREG_JOIN_1", "^\[[0-9]{2}:[0-9]{2}\] ([^<>]+) \((.+@.+)\) joined #");
define("EREG_JOIN_2", ".");
define("EREG_PART_1", "^\[[0-9]{2}:[0-9]{2}\] ([^<>]+) \((.+@.+)\) left #");
define("EREG_PART_2", ".");
define("EREG_KICK_1", "^\[[0-9]{2}:[0-9]{2}\] ([^<>]+)!~*(.+@.+) kicked from #");
define("EREG_KICK_2", " by ([^<>]+)!~*(.+@.+)");
define("EREG_QUIT", "^\[[0-9]{2}:[0-9]{2}\] ([^<>]+) \((.+@.+)\) left irc: ?(.*)");
define("EREG_NICK_CHANGE", "^\[[0-9]{2}:[0-9]{2}\] Nick change: (.+) -> (.+)");
define("EREG_CHANNEL_HEADER", "^Channel #([a-zA-Z0-9-]+), ([0-9]+) members, mode (.+):");
define("EREG_CHANNEL_TOPIC", "^Channel Topic: (.*)");
define("EREG_CHANNEL_USERLIST_HEADER", "^ NICKNAME  HANDLE");
define("EREG_CHANNEL_USERLIST", "(.)(.{9}) (.{9}) (.{5}) (.) (.{3}) (.+)");
define("EREG_CHANNEL_FOOTER", "^End of channel info.");

class t0xirc_bot {

	var $channel, $bot_nick;
	
	function t0xirc_bot($bot_login="", $bot_pass="", $bot_host="81.2.144.126", $bot_port=36463) {

		$this->bot_login=$bot_login;
		$this->bot_pass=$bot_pass;
		$this->bot_host=$bot_host;
		$this->bot_port=$bot_port;
		$this->poll_delay=50000;

	}

	function set_host($bot_host) {

		$this->bot_host=$bot_host;
	
	}

	function set_port($bot_port) {

		$this->bot_port=$bot_port;
	
	}

	function set_login($bot_login) {

		$this->bot_login=$bot_login;
	
	}

	function set_pass($bot_pass) {

		$this->bot_pass=$bot_pass;
	
	}

	function set_poll_delay($poll_delay) {

		$this->poll_delay=$poll_delay;
	
	}

	function send_raw($s) {

		fputs($this->fp, "$s\n");
	
	}
	
	function connect() {

		$this->fp=fsockopen($this->bot_host, $this->bot_port, &$errno, &$errstr, 10);

		if (!$this->fp) return(false);

		while (!strstr(fgets($this->fp, 4096),"ickname")) usleep(50000);
		if (feof($this->fp)) return(false);
		$this->send_raw($this->bot_login);

		while (!strstr(fgets($this->fp, 4096),"asswor") && !feof($this->fp)) usleep(50000);
		if (feof($this->fp)) return(false);
		$this->send_raw($this->bot_pass);

		while (!strstr(fgets($this->fp, 4096),"joined") && !feof($this->fp)) usleep(50000);
		if (feof($this->fp)) return(false);
		$this->send_raw(".console +mpjkco");
		set_socket_blocking($this->fp, false);
	
//		$this->update_channel();

		return(true);
	
	}

	function disconnect() {

		$this->send_raw(".quit");
		while (!feof($this->fp)) fgets($this->fp);
		fclose($this->fp);
	
	}

	
	function poll() {

		$tm_arr=getdate();

		if (!$this->poll_once) {

			$this->lday=$tm_arr["mday"];
			$this->lhour=$tm_arr["hours"];
			$this->lmin=$tm_arr["minutes"];
			$this->lsec=$tm_arr["seconds"];
		
			$this->poll_once=true;

		}
		
		if ($this->lday!=$tm_arr["mday"]) {

			if (method_exists($this, "on_timer_day")) $this->on_timer_day();
			if (isset($this->callbacks[TCB_TIMER_DAY])) $this->callbacks["timer_day"]();
			$this->lday=$tm_arr["mday"];
		
		}

		if ($this->lhour!=$tm_arr["hours"]) {

			if (method_exists($this, "on_timer_hour")) $this->on_timer_hour();
			if (isset($this->callbacks[TCB_TIMER_HOUR])) $this->callbacks["timer_hour"]();
			$this->lhour=$tm_arr["hours"];
		
		}

		if ($this->lmin!=$tm_arr["minutes"]) {

			if (method_exists($this, "on_timer_minute")) $this->on_timer_minute();
			if (isset($this->callbacks[TCB_TIMER_MINUTE])) $this->callbacks["timer_minute"]();
			$this->lmin=$tm_arr["minutes"];
		
		}

		if ($this->lsec!=$tm_arr["seconds"]) {

			if (method_exists($this, "on_timer_second")) $this->on_timer_second();
			if (isset($this->callbacks[TCB_TIMER_SECOND])) $this->callbacks["timer_second"]();
			$this->lsec=$tm_arr["seconds"];
		
		}

		if (method_exists($this, "on_timer_loop")) $this->on_timer_loop();
		if (isset($this->callbacks["timer_loop"])) $this->callbacks[TCB_TIMER_LOOP]();

		if ($s=rtrim(fgets($this->fp, 4096))) {
			
			$arstr=explode("\n", $s);
			
			while (list($key, $str)=each($arstr)) {

				if (ereg(EREG_PUBMSG, $str, &$res)) {

					// PUBMSG

					$q_nick=$res[1];
					$q_text=trim($res[2]);

					if (method_exists($this, "on_pubmsg")) $this->on_pubmsg($q_nick, $q_text);
					if (isset($this->callbacks["pubmsg"])) $this->callbacks[TCB_PUBMSG]($q_nick, $q_text);
				
				} else if (ereg(EREG_PUBACT, $str, &$res)) {
					
					// PUB ACTION	

					$q_nick=$res[1];
					$q_text=trim($res[2]);

					if (method_exists($this, "on_pubact")) $this->on_pubact($q_nick, $q_text);
					if (isset($this->callbacks["pubact"])) $this->callbacks[TCB_PUBACT]($q_nick, $q_text);
					
				} else if (ereg(EREG_PRIVMSG, $str, &$res)) {

					// PRIVMSG
					
					$q_nick=$res[1];
					$q_text=trim($res[2]);

					if (method_exists($this, "on_privmsg")) $this->on_privmsg($q_nick, $q_text);
					if (isset($this->callbacks["privmsg"])) $this->callbacks[TCB_PRIVMSG]($q_nick, $q_text);

				} else if (ereg(EREG_PRIVACT_1.$this->bot_nick.EREG_PRIVACT_2, $str, &$res)) {
					
					// PRIV ACTION	

					$q_nick=$res[1];
					$q_text=trim($res[2]);

					if (method_exists($this, "on_privact")) $this->on_privact($q_nick, $q_text);
					if (isset($this->callbacks["privact"])) $this->callbacks[TCB_PRIVACT]($q_nick, $q_text);

				} else if (ereg(EREG_SELF_PUBMSG_1.$this->channel["name"].EREG_SELF_PUBMSG_2, $str, &$res)) {

					// SELF TALK FROM OTHER PROCESS

					$q_nick=$res[1];
					$q_text=trim($res[2]);

					if (method_exists($this, "on_selfpubmsg")) $this->on_selfpubmsg($q_nick, $q_text);
					if (isset($this->callbacks["selfpubmsg"])) $this->callbacks[TCB_SELF_PUBMSG]($q_nick, $q_text);
					
				} else if (ereg(EREG_CHANNEL_HEADER, $str, &$res)) {
					
					// CHANNEL INFO

					$this->channel["name"]=$res[1];
					$this->channel["members_count"]=(int)$res[2];
					$this->channel["mode"]=$res[3];
				
				} else if (ereg(EREG_CHANNEL_TOPIC, $str, &$res)) {
					
					// CHANNEL INFO

					$this->channel["topic"]=trim($res[1]);
				
				} else if (ereg(EREG_CHANNEL_USERLIST_HEADER, $str, &$res)) {
					
					// CHANNEL INFO BEGIN

					$this->channel["members_tmp_loading"]=true;
				
				} else if (ereg(EREG_CHANNEL_FOOTER, $str, &$res)) {
					
					// CHANNEL INFO END

					$this->channel["members_tmp_loading"]=false;
					$this->channel["members"]=$this->channel["members_tmp"];
					unset($this->channel["members_tmp"]);
					$this->channel["pending_update"]=false;

				} else if (ereg(EREG_JOIN_1.$this->channel["name"].EREG_JOIN_2, $str, &$res)) {

					// JOIN

					$q_nick=$res[1];
					$q_userhost=trim($res[2]);

					$this->update_channel();

					if (method_exists($this, "on_join")) $this->on_join($q_nick, $q_userhost);
					if (isset($this->callbacks["join"])) $this->callbacks[TCB_JOIN]($q_nick, $q_userhost);
				
				} else if (ereg(EREG_PART_1.$this->channel["name"].EREG_PART_2, $str, &$res)) {

					// PART

					$q_nick=$res[1];
					$q_userhost=trim($res[2]);

					$this->update_channel();

					if (method_exists($this, "on_part")) $this->on_part($q_nick, $q_userhost);
					if (isset($this->callbacks["part"])) $this->callbacks[TCB_PART]($q_nick, $q_userhost);
				
				} else if (ereg(EREG_KICK_1.$this->channel["name"].EREG_KICK_2, $str, &$res)) {

					// KICK

					$q_nick1=$res[1];
					$q_nick2=$res[3];

					$this->update_channel();

					if (method_exists($this, "on_kick")) $this->on_kick($q_nick1, $q_nick2);
					if (isset($this->callbacks["kick"])) $this->callbacks[TCB_KICK]($q_nick1, $q_nick2);
					
				
				} else if (ereg(EREG_QUIT, $str, &$res)) {

					// QUIT

					$q_nick=$res[1];
					$q_userhost=$res[2];
					$q_text=trim($res[3]);

					$this->update_channel();

					if (method_exists($this, "on_quit")) $this->on_quit($q_nick, $q_text);					
					if (isset($this->callbacks["quit"])) $this->callbacks[TCB_QUIT]($q_nick, $q_text);
				
				} else if (ereg(EREG_NICK_CHANGE, $str, &$res)) {
					
					// NICK CHANGE

					$q_nick=$res[1];
					$q_nick2=trim($res[2]);

					$this->update_channel();

					if (method_exists($this, "on_nickch")) $this->on_nickch($q_nick, $q_nick2);					
					if (isset($this->callbacks["nickch"])) $this->callbacks[TCB_NICK_CHANGE]($q_nick, $q_nick2);
					
				}

				if (($this->channel["members_tmp_loading"]) && (!(ereg("^ NICKNAME  HANDLE", $str, &$res)))) {

					if (ereg(EREG_CHANNEL_USERLIST, $str, &$res)) {

						$tmp_m["prefix"]=trim($res[1]);
						$tmp_m["nickname"]=trim($res[2]);
						$tmp_m["handle"]=trim($res[3]);
						$tmp_m["join"]=trim($res[4]);
						$tmp_m["level"]=trim($res[5]);
						$tmp_m["idle"]=trim($res[6]);
						$tmp_m["userathost"]=trim($res[7]);

						if (substr($tmp_m["userathost"], 0, 3)=="<- ") {

							$this->bot_nick=$tmp_m["nickname"];
						
						} else {
						
							$tmp_a=explode("@", $tmp_m["userathost"]);
							$tmp_m["user"]=$tmp_a[0];
							$tmp_m["host"]=$tmp_a[1];

						}

						if ($tmp_m["prefix"]=="@") $tmp_m["op"]=true;

						$this->channel["members_tmp"][trim($res[2])]=$tmp_m;

					}
					
				}

			}

		}

	}

	function run($loops=0) {

		if (!$loops) $unlim=true;

		while ($unlim || $loops--) {

			$this->poll();
			usleep($this->poll_delay);
		
		}
	
	}

	function register_callback($type, $s) {

		$this->callbacks[$type]=$s;
	
	}

	function unregister_callback($type) {

		unset($this->callbacks[$type]);
	
	}

	function say($msg, $channel="") {

		$this->send_raw(".say $channel $msg");
	
	}

	function msg($nick, $msg) {

		$this->send_raw(".msg $nick $msg");

	}

	function pubact($msg, $channel="") {

		$this->send_raw(".act $channel $msg");

	}

	function kick($nick, $channel="") {

		$this->send_raw(".kick ".($channel?$channel." ":"").$nick);
	
	}

	function op($nick, $channel="") {

		$this->send_raw(".op ".($channel?$channel." ":"").$nick);
	
	}

	function deop($nick, $channel="") {

		$this->send_raw(".deop ".($channel?$channel." ":"").$nick);
	
	}

	function voice($nick, $channel="") {

		$this->send_raw(".voice ".($channel?$channel." ":"").$nick);
	
	}

	function devoice($nick, $channel="") {

		$this->send_raw(".devoice ".($channel?$channel." ":"").$nick);
	
	}


	function set_topic($topic) {

		$this->send_raw(".topic $topic");
	
	}

	function use_channel($chan) {

		$this->send_raw(".console $chan");
		$this->update_channel();

	}
	
	function botnick($nick) {

		// This will only work if you have the botnick.tcl eggdrop extension installed

		$this->send_raw(".botnick $nick");
	
	}

	function update_channel() {

		$this->send_raw(".channel");
		$this->channel["pending_update"]=true;

		while ($this->channel["pending_update"]) {

			$this->poll();
			usleep($this->poll_delay);
		
		}

	}

}

?>
