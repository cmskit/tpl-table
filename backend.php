<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
<title><?php echo $projectName;?>-backend</title>
<meta charset="utf-8" />

<!-- prevent Browser-Caching -->
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />

<!-- tell the browser what we mean with script-/style-Tags-->
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />

<!-- prevent zoom-out -->
<meta name="viewport" content="width=device-width, initial-scale=1" /> 

<?php
	
	echo '
<script>
	var projectName = "'.$projectName.'",
		objectName = '.($objectName?'"'.$objectName.'"':'false').',
		settings = '.@json_encode($_SESSION[$projectName]['settings']).' || {},
		store = {},
		columns = '.(!empty($_GET['columns']) ? '['.$_GET['columns'].']' : 'settings.templates.default.columns').', // (default) width/height of columns [north, west, east, south, is_internal_flag]
		lang = "'.$lang.'",
		userId = "'.$_SESSION[$projectName]['special']['user']['id'].'",
		userName = "'.$_SESSION[$projectName]['special']['user']['prename'].' '.$_SESSION[$projectName]['special']['user']['lastname'].'",
		userProfiles = '.json_encode($_SESSION[$projectName]['special']['user']['profiles']).', 
		langLabels = {'.$jsLangLabels.'},
		client = '.json_encode($_SESSION[$projectName]['client']).';
</script>
	';
?>

<link rel="icon" type="image/png" href="inc/css/icon.png" />
<link rel="stylesheet" type="text/css" id="mainTheme" href="../vendor/cmskit/jquery-ui/themes/<?php echo end($_SESSION[$projectName]['settings']['interface']['theme'])?>/jquery-ui.css" />
<link rel="stylesheet" type="text/css" id="baseTheme" href="templates/table/jquerytable/css/jqueryui/jtable_jqueryui.css" />

<script src="../vendor/cmskit/jquery-ui/jquery.min.js"></script>
<script src="../vendor/cmskit/jquery-ui/jquery-ui.js"></script>
<script src="templates/table/jquerytable/jquery.jtable.js" ></script>
<script src="templates/table/jquerytable/jquery.jtable.search.js" ></script>
<script src="templates/table/jquerytable/localization/jquery.jtable.<?php echo $lang?>.js"></script>
<script src="templates/table/js/functions.js" ></script>
<style>
body{
	font-size: .8em;
}
.subTableHandler{cursor:pointer;}
.subOpen{background-color:#ccc;}
</style>

</head>
<body>


<!-- status-messagebox -->
<div id="messagebox"></div>

<div id="iHead" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		
	<div id="iHeadRight" style="float:right">
		
		<?php
		echo $uwizSelect;
		echo '<button type="button" id="logoutButton" rel="power" onclick="logout()">'.L('logout').'</button>';

		?>
		
	</div>

	<div id="iHeadLeft">
		<?php

		// draw Logo if available
		if (file_exists($projectPath.'/objects/logo.png'))
		{
			echo '<img id="logo" style="height:27px;float:left;margin:0 10px 0 0;" src="'.$projectPath.'/objects/logo.png" />';
		}


        // draw Object-Selector
        echo $dropdowns['objectSelect'];

        // draw Template-Selector if needed
        echo $dropdowns['tplSelect'];
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		
		function prepareField ($k, $v)
		{
			global $lang, $count;
			$a = array();
			
			$a['key'] = false;
			$a['title'] = isset($v['lang'][$lang]) ? $v['lang'][$lang]['label'] : $k;
			
			// translate Object-Types to Field-Properties
			$types = array(
				'INTEGER'	=> array('list'=>true, 'edit'=>true, 'create'=>true),
				'BOOL'		=> array('type'=>'checkbox', 'searchable' => false, 'list'=>true, 'edit'=>true, 'create'=>true, 'values'=>array('0'=>'<i>'.L('off').'</i>','1'=>'<b>'.L('on').'</b>')),	
				'FLOAT'		=> array('list'=>true, 'edit'=>true, 'create'=>true),
				'VARCHAR'	=> array('list'=>true, 'edit'=>true, 'create'=>true),
				'TEXT'		=> array('type'=>'textarea', 'list'=>true, 'edit'=>true, 'create'=>true),
				'DATE'		=> array('type'=>'date', 'list'=>true, 'edit'=>true, 'create'=>true, 'displayFormat' => 'yy-mm-dd'),
				'DATETIME'	=> array('type'=>'', 'list'=>true, 'edit'=>true, 'create'=>true),
				'TIMESTAMP'	=> array('list'=>true, 'edit'=>true, 'create'=>true),
				'YEAR'		=> array('list'=>true, 'edit'=>true, 'create'=>true),
				'MODEL'		=> array('sorting'=>false, 'list'=>false, 'edit'=>false, 'create'=>false),
				'BLOB'		=> array('sorting'=>false, 'list'=>false, 'edit'=>false, 'create'=>false),
			);
			
			foreach ($types[$v['type']] as $tk => $tv)
			{
				$a[$tk] = $tv;
			}
			
			// change some Field-Properties
			if ($k == 'id')
			{
				$a['key'] = true;
			}
			
			if (substr($k,-2)=='id' || substr($k,-4)=='sort' || substr($k,0,4)=='tree')
			{
				$a['create'] = false;
				$a['edit'] = false;
				$a['list'] = false;
				$a['sorting'] = false;
				$a['searchable'] = false;
			}
			
			// detect Select-Fields & prepare their Options
			if (isset($v['add']['wizard']) && isset($v['add']['option']) && $v['add']['wizard']=='select')
			{
				$ol = explode('|', $v['add']['option']);
				$a['options'] = array();
				foreach($ol as $o)
				{ 
					$lbl = $o;
					$t = '';
					// extract Label if any [...]
					if(preg_match( '/\[(.*?)\]/', $o, $match)===1) {
						$lbl = $match[1];
						$o = str_replace($match[0],'',$o);
					}
					// extract Title if any (...)
					if(preg_match( '/\(([^)]+)\)/', $o, $match)===1) {
						$t = $match[1];
						$o = str_replace($match[0],'',$o);
					}
					$a['options'][$o] = $lbl;
				}
				
			}
			
			// show only the first x Columns
			if ($a['list'] == false) $count--;
			if ($count > 8) $a['visibility'] = 'hidden';
			
			
			return $a;
		}// function prepareField END
		
		
		// collect Fields of the main Object
		$mainObject = array();
		$count = 0;
		foreach ($_SESSION[$projectName]['objects'][$objectName]['col'] as $k => $v)
		{
			$mainObject[$k] = prepareField($k, $v);
			$count++;
		}
		
		// collect Fields of the Sub-Objects
		$subObjects = array();
		$objectProps = array();
		$objectProps[$objectName] = array((isset($_SESSION[$projectName]['objects'][$objectName]['lang'][$lang])?$_SESSION[$projectName]['objects'][$objectName]['lang'][$lang]:$objectName), false);
		
		foreach ($_SESSION[$projectName]['objects'][$objectName]['rel'] as $k => $v)
		{
			$subObjects[$k] = array();
			$objectProps[$k] = array((isset($_SESSION[$projectName]['objects'][$k]['lang'][$lang]) ? $_SESSION[$projectName]['objects'][$k]['lang'][$lang] : $k), $v);
			$count = 1;
			
			// add a special Field showing if the Entry is connected
			$ca = array(
				'type' => 'BOOL',
				'lang' => array($lang => array('label'=>L('connected'))),
			);
			
			$subObjects[$k]['__connected__'] = prepareField('__connected__', $ca);
			$subObjects[$k]['__connected__']['sorting'] = false;
			$subObjects[$k]['__connected__']['searchable'] = false;
			$subObjects[$k]['__connected__']['list'] = false;
			// special Field END
			
			
			foreach ($_SESSION[$projectName]['objects'][$k]['col'] as $sk => $sv)
			{
				$subObjects[$k][$sk] = prepareField($sk, $sv);
				$count++;
			}
		}
		
		?>
	</div>
	
</div><!--iHead END-->


<div id="main" style="width:600px">

</div>

<script>
	var mainObject 	= <?php echo json_encode($mainObject)?>;
	var subObjects 	= <?php echo json_encode($subObjects)?>;
	var objectProps = <?php echo json_encode($objectProps)?>;
</script>
</body>
</html>
