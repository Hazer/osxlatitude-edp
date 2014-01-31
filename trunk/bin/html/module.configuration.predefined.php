<?php
include_once "../functions.inc.php";
include_once "../config.inc.php";

include "header.inc.php";

//Get server vars
global $modelID;

$vendor 	= $_GET['vendor'];	if ($vendor == "") 	{ $vendor 	= $_POST['vendor']; }
$serie 		= $_GET['serie'];	if ($serie == "") 	{ $serie 	= $_POST['serie']; }
$modelID 	= $_GET['model'];	if ($modelID == "") { $modelID 	= $_POST['model']; }
$action 	= $_GET['action']; 	if ($action == "") 	{ $action 	= $_POST['action']; }

//-------------------------> Do build page starts here
if ($action == 'dobuild') {
	
	echo "<span class='console'>";

	if ($modelID == "") { echo "modelID is empty"; exit; }

	//Generate a multi dim. array used during the build process
	global $modeldb;

	$modeldb = array(
		//This one have to be empty, its used for when we do custom builds...
		array( 	
			'name' 			     => $_POST['name'], 
			'desc' 			     => $_POST['desc'],
			'useEDPExtentions' 	=> $_POST['useEDPExtentions'], 		
			'useEDPDSDT' 		=> $_POST['useEDPDSDT'],			
			'useEDPSSDT' 		=> $_POST['useEDPSSDT'],			
			'useEDPSMBIOS' 		=> $_POST['useEDPSMBIOS'], 			
			'useEDPCHAM' 		=> $_POST['useEDPCHAM'], 
			'useIncExtentions' 	=> $_POST['useIncExtentions'], 		
			'useIncDSDT' 		=> $_POST['useIncDSDT'],			
			'useIncSSDT' 		=> $_POST['useIncSSDT'],			
			'useIncSMBIOS' 		=> $_POST['useIncSMBIOS'], 			
			'useIncCHAM' 		=> $_POST['useIncCHAM'],            		
			'ps2pack' 		     => $_POST['ps2pack'],
			'batterypack'		 => $_POST['batterypack'],
			'ethernet'		     => $_POST['ethernet'],
			'wifipack'		     => $_POST['wifipack'],
			'audiopack'		     => $_POST['audiopack'],                    		                      		                      		
			'fakesmc'			 => $_POST['fakesmc'],
			'nullcpupwr'			 => $_POST['nullcpupwr'],
			'applecpupwr'			 => $_POST['applecpupwr'],
			'sleepenabler'			 => $_POST['sleepenabler'],
			'emupstates'			 => $_POST['emupstates'],
			'voodootsc'			 => $_POST['voodootsc'],
			'noturbo'			 => $_POST['noturbo'],
			'ACPICodec' 		 => $_POST['ChamModuleACPICodec'],
			'FileNVRAM' 		 => $_POST['ChamModuleFileNVRAM'],
			'KernelPatcher' 	 => $_POST['ChamModuleKernelPatcher'],
			'Keylayout' 		 => $_POST['ChamModulekeylayout'],
			'klibc' 			 => $_POST['ChamModuleklibc'],
			'Resolution'         => $_POST['ChamModuleResolution'],
			'Sata' 			     => $_POST['ChamModuleSata'],
			'uClibcxx' 		     => $_POST['ChamModuleuClibcxx'],
			'HDAEnabler' 		 => $_POST['ChamHDAEnabler'],
			'customCham' 		 => $_POST['customCham'],
			'updateCham' 		 => $_POST['updateCham'],
			'fixes' 		     => $_POST['fixes'],
			'optionalpacks'	     => $_POST['optionalpacks']                    		 
		),
	);

	global $modelID;
	global $modelName;
	//id of modeldb array which is '0' for a model
	global $modeldbID;
	$modeldbID = "0";

	$modelName = $modeldb[$modeldbID]["name"];

	$builder->EDPdoBuild();	
}

//-------------------------> Here starts the Vendor and model selector - but only if $action is empty

if ($action == "") {

	//Fetch standard model info needed for the configuration of the choosen model to build
		$stmt = $edp_db->query("SELECT * FROM modelsdata where id = '$modelID'");
		$stmt->execute();
		$result = $stmt->fetchAll(); $mdrow = $result[0];
		
	//Write out the top menu
	echoPageItemTOP("icons/big/config.png", "Select a model your wish to configure for:");

	echo "<div class='pageitem_bottom'>\n";
	echo "EDP's internal database contains 'best practice' schematics for 80+ systems - this makes it easy for to to choose the right configuration - however - you allways have the option to ajust the schematics before doing a build. <br><br>Doing a build means that EDP will copy a combination of kexts, dsdt, plists needed to boot your system.";

	include "header.inc.php";
	echo "<p><span class='graytitle'></span><ul class='pageitem'><li class='select'>";

	echo "<select name='vendor' id='vendor'>";
	
	if ($vendor == "") { echo "<option value='' selected>&nbsp;&nbsp;Select vendor...</option>\n"; } else { echo "<option value='' selected>&nbsp;&nbsp;Select vendor and type...</option>\n"; }

	echo builderGetVendorValues(); // For series and model we are using jquery

	echo "</select><span class='arrow'></span> </li>";

	echo "<li id='serie-container' class='select hidden'><td><select id='serie' name='serie'>";

	echo "</select><span class='arrow'></span> </li>";					

	echo "<li id='model-container' class='select hidden'><td><select id='model' name='model'>";

	echo "</select><span class='arrow'></span> </li></ul>";

	echo '<div id="continue-container" class="hidden">';
	echo "<p><B><center>After clicking 'Continue' EDP will download the latest model data for your machine.<br>- This may take a few minuts -	</center></p><br>";
	echo "<ul class='pageitem'><li class='button'><input name='OK' type='button' value='Continue...' onclick='doConfirm();' /></li></ul></p>";
	echo '</div>';

	?>

	<style>
		.hidden {
			display: none;
		}
	</style>
	<script>
		jQuery('#vendor').change(function() {
			var vendor = jQuery('#vendor option:selected').val();

			console.log('Selected vendor: ' + vendor);

			if (vendor == '') {
				jQuery('#serie-container, #model-container, #continue-container').addClass('hidden');
				return;
			}

			jQuery.get('workerapp.php', { action: 'builderSerieValues', vendor: vendor }, function(data) {
				jQuery('#serie').empty().append(data).val('');
				jQuery('#serie-container').removeClass('hidden');
				jQuery('#model-container, #continue-container').addClass('hidden');
			});
		});

		jQuery('#serie').change(function() {
			var vendor = jQuery('#vendor option:selected').val();
			var serie  = jQuery('#serie option:selected').val();

			console.log('Selected serie: ' + serie);

			if (vendor == '' || serie == '') {
				jQuery('#model-container, #continue-container').addClass('hidden');
				return;
			}

			jQuery.get('workerapp.php', { action: 'builderModelValues', vendor: vendor, serie: serie }, function(data) {
				jQuery('#model').empty().append(data);
				jQuery('#model-container').removeClass('hidden');
			});
		});

		jQuery('#model').change(function() {
			var vendor = jQuery('#vendor option:selected').val();
			var serie  = jQuery('#serie option:selected').val();
			var model  = jQuery('#model option:selected').val();

			console.log('Selected model: ' + model);

			if (vendor == '' || serie == '' || model == '') {
				jQuery('#continue-container').addClass('hidden');
				return;
			}

			jQuery('#continue-container').removeClass('hidden');
		});
	</script>				

	<?php }

//<-------------------- Here stops the vendor and model selector		

//--------------------> Here starts the build confirmation page 
//Check if $action was set via GET or POST - if it is set, we asume that we are going to confirm the build
	if ($action == "confirm") {
		
		//Fetch standard model info needed for the configuration of the choosen model to build
		$stmt = $edp_db->query("SELECT * FROM modelsdata where id = '$modelID'");
		$stmt->execute();
		$bigrow = $stmt->fetchAll(); $mdrow = $bigrow[0];

		//Download model data
		/*$modelName = $mdrow[name];
		echo "<div id='model_download' style='display: none'>";
		svnModeldata("$modelName");
		echo "</div>";*/
		
		//Load the tabs
		echo "<script> $(function() { $( \"#tabs\" ).tabs(); }); </script>\n";
		
		echo "<form action='module.configuration.predefined.php' method='post'>";
		echoPageItemTOP("http://www.osxlatitude.com/wp-content/themes/osxlatitude/img/edp/modelpics/$mdrow[name].png", "$mdrow[desc]");
		
		//Show the tabs bar ?>
		<div id="tabs">
			<div id="menutabs">
				<ul>
					<li><a href="#tabs-0">Overview</a></li>
					<li><a href="#tabs-1">Kext / Drivers</a></li>
					<li><a href="#tabs-2">CPU & Power</a></li>
					<li><a href="#tabs-3">Chameleon</a></li>
					<li><a href="#tabs-4">Fixes</a></li>
					<li><a href="#tabs-5">Optional</a></li>
				</ul>
			</div>
			<?php

			echo "<div class='pageitem_bottom'><br>\n";

			//Include tabs
			include "include/module.configuration.overview.inc.php";
			include "include/module.configuration.kexts.inc.php";
			include "include/module.configuration.cpu.inc.php";
			include "include/module.configuration.chameleon.inc.php";		
			include "include/module.configuration.fixes.inc.php";
			include "include/module.configuration.optional.inc.php";		

			//Send standard vars
			echo "<input type='hidden' name='action' value='dobuild'>";
			echo "<input type='hidden' name='name' value='$mdrow[name]'>";
			echo "<input type='hidden' name='desc' value='$mdrow[desc]'>";
			echo "<input type='hidden' name='model' value='$modelID'>";

			echo "</div><br>";
			echo "<ul class='pageitem'><li class='button'><input name='Submit input' type='submit' value='Do build!' /></li></ul><br><br>\n";
			echo "</form>";		


			exit;
		}
	//<------------------------------ Here ends the model confirmation page
		?>

		<script>
		function doConfirm() {
			var vendor = '<?php echo "$vendor";?>';
			var a = document.getElementById("model");
			var model = a.options[a.selectedIndex].value;
			if (model == "") { alert('Please select a model before continuing..'); return; }
			document.location.href = 'module.configuration.predefined.php?vendor='+vendor+'&model='+model+'&action=confirm';		
		}
		function showType() {
			var a = document.getElementById("vendor");
			var vendor = a.options[a.selectedIndex].value;	
			var b = document.getElementById("serie");
			var serie = b.options[b.selectedIndex].value;
			document.location.href = 'module.configuration.predefined.php?vendor='+vendor+'&serie='+serie+'';
		}
		function showSerie() {
			var a = document.getElementById("vendor");
			var vendor = a.options[a.selectedIndex].value;	
			document.location.href = 'module.configuration.predefined.php?vendor='+vendor+'';			
		}
		</script>

	</body>

	</html>
