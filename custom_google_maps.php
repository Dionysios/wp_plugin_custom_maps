<?php
/*
Plugin Name: Google Maps Custom with mutliple markers
Description: This plugin allows you to add one or more maps to your page/post using shortcodes, including having multiple markers per map :)
Version: 1.0
Author: Dion Papathanopoulos
*/

// Add the google maps api to header
add_action('wp_head', 'MultipleMarkerMap_header');

function MultipleMarkerMap_header() {
	?>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<?php
}

//function to return all address

	function get_address(){		
					mysql_connect(get_option('dbhost'), get_option('dbuser'), get_option('dbpwd')) or
						die("Could not connect: " . mysql_error());
							mysql_select_db(get_option('dbname'));
							$result = mysql_query("SELECT Address,name FROM wp_terms where term_group = '1'");
					$colum = "";
					$addr = "";		
						while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
							$addr = $row[0];
							$name = $row[1];
					$colum .= getCoordinates($addr);
							//printf("%s", $addr);
							//	printf("%s", $colum); 
								}	
								mysql_free_result($result);
								$coords = $colum;
							//	$coords = implode("|", $colum);
								//		printf("%s", $coords); 
								$coords = explode(",",$colum);
									$coordsTmp = "";
										for($i = 0;$i < count($coords);$i++){
											$coordsTmp .='|'.$coords[$i].','.$coords[$i+1].' ';
										}
							echo $name;					
					$map4 = create_Map($coordsTmp,$addr);
			return $map4;
		}


// Main function to generate google map
		function create_Map($coordsTmp,$name) {
		
				// default atts
					$attr = shortcode_atts(array(	
									'lat'   => '57.700662', 
									'lon'    => '11.972609',
									'id' => 'map',
									'z' => '14',
									'w' => '530',
									'h' => '450',
									'maptype' => 'ROADMAP',
									'marker' => $coordsTmp								
									), $attr);
												
				$map = '
				<div id="map" style="width:530px;height:450px;border:1px solid gray;"></div><br>
				
				<script type="text/javascript">
				var infowindow = null;
				var latlng=new google.maps.LatLng(57.700662,11.972609);
					var latlngw  = new google.maps.LatLng(' . $attr['lat'] . ', ' . $attr['lon'] . ');
					var myOptions = {
						zoom: 13,
						center: latlng,
						mapTypeId: google.maps.MapTypeId.' . $attr['maptype'] . '
					};
					var map = new google.maps.Map(document.getElementById("map"),myOptions);';
		
				   $map .=' var sites = [';
						//marker: show if address is not specified
						if ($attr['marker'] != ''){
							$markers = explode("|",$attr['marker']);
							for($i = 0;$i < count($markers);$i ++){
								
								$markerTmp=$markers[$i];
								$marker= explode(",",$markerTmp);
									if (count($marker)>3) { 
									$markerTmp2 .='['.$marker[0].',' .$marker[1].',\'' . $marker[2] . '\',\'' . $marker[3] . '\'],';
									} else {
									$markerTmp2 .='['.$marker[0].',' .$marker[1].',\'' . $marker[2] . '\',null],';
										}
						}
					  }
	  
					 $markerTmp2=substr ($markerTmp2,0,strlen ( $markerTmp2 )-1);
					 $map .=$markerTmp2;
					 $map .='];';
					 $map .='';
					 $map .=' for (var i = 0; i < sites.length; i++) {';
					 $map .=' var site = sites[i];';
					 $map .=' var siteLatLng = new google.maps.LatLng(site[0], site[1]);';	
					 $map .=' var markerimage  = site[3];';
					 
					 $map .=' var marker = new google.maps.Marker({';
					 $map .=' position: siteLatLng, ';
					 $map .=' map: map,title:"'.$addr.'",';
					 $map .=' icon: markerimage,';
					 $map .=' html: "Hello dude" }); 
					 
					 var infowindow = new google.maps.InfoWindow({
							content: "Hello dude"
							});

						var marker3 = new google.maps.Marker({
								position: latlng,
								map: map,
								title:"Uluru (Ayers Rock)"
						});

					 
								google.maps.event.addListener(marker3, "click", function () {					
									infowindow.open(map,marker3);
								})
					;}
								</script>';
						return $map;
					}	
		
	
	
	function getCoordinates($addr){
		$urladdress = urlencode($addr);
		$Base_url = "http://maps.google.com/maps/geo?q=";
		$urlParts = "&output=xml";
		$urlrequest = $Base_url . $urladdress . $urlParts;
		$xml = simplexml_load_file($urlrequest);
		
				foreach ($xml->Response->Placemark as $value){
				
					$GeoFindAdd = $value->address;
					$GeoFindCords = $value->Point->coordinates;
					$comma = ',';
					$GeoFindCords = $GeoFindCords.$comma;
				}
		//		echo $GeoFindCords;
								$coords = explode(",", $GeoFindCords);
								$comma = ',';
								//	echo $coords[0]; 
									 
							//		echo "</br>";
								//	echo $coords[1];
								$Cords = $coords[1].$comma.$coords[0].$comma; 
								return $Cords;
	}
		
	add_shortcode('Cord','get_address');

?>
