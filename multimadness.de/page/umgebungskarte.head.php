<?php

# TODO: Hier die Klick-PINs hin, die Infos öffnen!

/*
        $output .= "var loc_pers_".$row->id." = new Microsoft.Maps.Location(".$row->lati.", ".$row->long.");\n";
				$output .= "var pin_pers_".$row->id." = new Microsoft.Maps.Pushpin(loc_pers_".$row->id.", {icon: 'images/pointer_gruen.png', draggable: false});\n";
				$output .= "Microsoft.Maps.Events.addHandler(pin_pers_".$row->id.", 'click', function(e){";
				$output .= "  document.getElementById('box_details').innerHTML = '<b><i class=\"fa fa-user\"></i>".htmlspecialchars($row->anrede." ".$row->titel." ".$row->vorname." ".$row->nachname)."</b><br />";
				if ($row->schwerpunktbezeichnungen != "") $output .= "  <span class=\"titel\">".htmlspecialchars($row->schwerpunktbezeichnungen)."</span><br />";
				if ($row->gelbfieberimpfung == 1) $output .= "  Gelbfieberimpfung ermächtigung<br>";
				$output .= "  <br />";
				if ($row->schwerpunkte_fromdb != "") {
					$output .= "	<ul>";
					$punkte = explode(",", $row->schwerpunkte_fromdb);
					foreach ($punkte as $pkey => $pvalue)
						$output .= "		<li>".htmlspecialchars($pvalue)."</li>";
					$output .=" 	</ul>";
				}
				$output .= "  ".htmlspecialchars($row->name)."<br />";
				$output .= "  ".htmlspecialchars($row->strasse." ".$row->hausnummer)."<br />";
				$output .= "  ".htmlspecialchars($row->plz." ".$row->ort)."<br />";
				if ($row->telefon != "") $output .= "  ".htmlspecialchars($row->telefon)."<br />";
				if ($row->website != "") $output .= "  ".htmlspecialchars($row->website)."<br />";
				$output .= "  <br />";
				if ($row->zusatzinformationen != "") $output .= "  Zusatzinformationen:<br />".htmlspecialchars($row->zusatzinformationen)."<br />";
				$output .= "  '});\n";
				$output .= "map.entities.push(pin_pers_".$row->id.");\n";
*/
?>
<script charset="UTF-8" type="text/javascript" src="https://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0&mkt=de-DE&callback=GetMap"></script>
<script language="javascript">

	function GetMap() {
										
		<?php
		# Karten zentrieren, wenn eine gültige PLZ / Ort gepostet wurde!
		if (isset($_POST['plz']) && strlen ($_POST['plz']) >= 4 && TRUE) {
			# Call BING Maps API
			$query_address = urlencode($_POST['plz']);
			$xml = simplexml_load_file('http://dev.virtualearth.net/REST/v1/Locations?q='.$query_address.',DE&o=xml&key='.BINGMAPS_KEY);
			$lati = $xml->ResourceSets->ResourceSet->Resources->Location->Point->Latitude;
			$long = $xml->ResourceSets->ResourceSet->Resources->Location->Point->Longitude;
			$center = $lati.", ".$long;
			$zoom = 10;
		} else {
			$center = "53.395078, 10.050611";
			$zoom = 15;
		}	?>
															
		// Load BING Map
		var mapOptions = {
			credentials: '<?= BINGMAPS_KEY; ?>',
			center: new Microsoft.Maps.Location(<?= $center; ?>),
			mapTypeId: Microsoft.Maps.MapTypeId.birdseye,
			disableBirdseye:true,
			zoom: <?= $zoom; ?>
		}
		var map = new Microsoft.Maps.Map(document.getElementById("mapDiv"), mapOptions);

		// Retrieve the location of the map center 
		var center = map.getCenter();
		// Add a pin to the center of the map
		//var pin = new Microsoft.Maps.Pushpin(center, {text: '1'}); 
		//map.entities.push(pin);
		
		
		var loc_halle = new Microsoft.Maps.Location(53.394634, 10.054609);
		var pin_halle = new Microsoft.Maps.Pushpin(loc_halle); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_halle, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_halle" ).slideDown( 100 );
		});
		map.entities.push(pin_halle);


		var loc_seeaction = new Microsoft.Maps.Location(53.393887, 10.059933);
		var pin_seeaction = new Microsoft.Maps.Pushpin(loc_seeaction); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_seeaction, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_seeaction" ).slideDown( 100 );			
		});
		map.entities.push(pin_seeaction);


		var loc_zelten = new Microsoft.Maps.Location(53.394298, 10.055186);
		var pin_zelten = new Microsoft.Maps.Pushpin(loc_zelten); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_zelten, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_zelten" ).slideDown( 100 );			
		});
		map.entities.push(pin_zelten);


		var loc_flunkyball = new Microsoft.Maps.Location(53.394391, 10.053966);
		var pin_flunkyball = new Microsoft.Maps.Pushpin(loc_flunkyball); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_flunkyball, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_flunkyball" ).slideDown( 100 );			
		});
		map.entities.push(pin_flunkyball);


		var loc_abbiegen = new Microsoft.Maps.Location(53.394407, 10.052557);
		var pin_abbiegen = new Microsoft.Maps.Pushpin(loc_abbiegen); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_abbiegen, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_abbiegen" ).slideDown( 100 );			
		});
		map.entities.push(pin_abbiegen);


		var loc_kreuzung = new Microsoft.Maps.Location(53.390195, 10.045532);
		var pin_kreuzung = new Microsoft.Maps.Pushpin(loc_kreuzung); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_kreuzung, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_kreuzung" ).slideDown( 100 );			
		});
		map.entities.push(pin_kreuzung);

		var loc_spkapo = new Microsoft.Maps.Location(53.394912, 10.038211);
		var pin_spkapo = new Microsoft.Maps.Pushpin(loc_spkapo); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_spkapo, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_spkapo" ).slideDown( 100 );			
		});
		map.entities.push(pin_spkapo);
		
		var loc_bahnhof = new Microsoft.Maps.Location(53.399021, 10.061430);
		var pin_bahnhof = new Microsoft.Maps.Pushpin(loc_bahnhof); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_bahnhof, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_bahnhof" ).slideDown( 100 );			
		});
		map.entities.push(pin_bahnhof);
		
		
		var loc_rewe = new Microsoft.Maps.Location(53.396714, 10.042276);
		var pin_rewe = new Microsoft.Maps.Pushpin(loc_rewe); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_rewe, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_rewe" ).slideDown( 100 );			
		});
		map.entities.push(pin_rewe);
		
		var loc_hamy = new Microsoft.Maps.Location(53.397541, 10.044245);
		var pin_hamy = new Microsoft.Maps.Pushpin(loc_hamy); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_hamy, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_hamy" ).slideDown( 100 );			
		});
		map.entities.push(pin_hamy);
		
		var loc_grillhaus = new Microsoft.Maps.Location(53.395092, 10.039865);
		var pin_grillhaus = new Microsoft.Maps.Pushpin(loc_grillhaus); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_grillhaus, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_grillhaus" ).slideDown( 100 );			
		});
		map.entities.push(pin_grillhaus);
		
		
		var loc_aldi = new Microsoft.Maps.Location(53.391297, 10.037914);
		var pin_aldi = new Microsoft.Maps.Pushpin(loc_aldi); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_aldi, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_aldi" ).slideDown( 100 );			
		});
		map.entities.push(pin_aldi);
		
		var loc_freibad = new Microsoft.Maps.Location(53,380005, 10,108791);
		var pin_freibad = new Microsoft.Maps.Pushpin(loc_freibad); // ggf. , {icon: 'images/pointer_gruen.png', draggable: false}
		Microsoft.Maps.Events.addHandler(pin_freibad, 'click', function(e){
			$( ".infobox_map" ).slideUp( 100 );
			$( "#box_freibad" ).slideDown( 100 );			
		});
		map.entities.push(pin_freibad);
		
	};

</script>