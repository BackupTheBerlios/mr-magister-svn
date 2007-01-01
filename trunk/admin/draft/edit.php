<html>
	<head>
		
		<title>Edit article with id <?php echo $_GET['id']; ?></title>
		<script language="JavaScript" type="text/javascript" src="/include/js/tiny_mce/tiny_mce.js"></script>
		<script language=javaschript" type="text/javascript">
		tinyMCE.init({
			mode : "textareas",
			theme : "advanced",
			plugins : "table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,zoom,searchreplace,print,contextmenu,paste,directionality,noneditable",
			theme_advanced_buttons1_add : "fontselect,fontsizeselect",
			theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator",
			theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
			theme_advanced_buttons3_add_before : "tablecontrols,separator",
			theme_advanced_buttons3_add : "emotions,advhr,separator,print,separator,ltr,rtl,separator",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			content_css : "/example_data/example_full.css",
		   plugin_insertdate_dateFormat : "%Y-%m-%d",
		   plugin_insertdate_timeFormat : "%H:%M:%S",
			extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			theme_advanced_resizing : true,
			apply_source_formatting : true,
			language: "de",
			file_browser_callback : "filebrowser_callback",
			convert_urls : false	
			});
		
		var fileBrowserWin;
		var fileBrowserFieldName;
				
		function filebrowser_callback(field_name, url, type, win)
		{
			window.open('/admin/old/link.php');
			fileBrowserFieldName = field_name;
			fileBrowserWin = win;
		}
		
		function fileBrowserReturn(url)
		{
			fileBrowserWin.document.forms[0].elements[fileBrowserFieldName].value = url;
		}
		</script>
	</head>
	<body>
	<?php
			require_once("../../include/include.php");
	
	mysql_connect($server, $user, $pass);
	
	mysql_select_db($database);
		
		$query = "SELECT text FROM article WHERE id =".$_GET['id'].";";
		
		$result = mysql_query($query);
		
		$row = mysql_fetch_array($result);
		
	?>
	
	<form action="">
		<textarea id="content" cols=100 rows=40><?php echo $row[0] ?></textarea>
	<button >
	</form>
	</body>
</html>

