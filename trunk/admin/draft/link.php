<?php
	
	require_once("../../include/include.php");
	
	mysql_connect($server, $user, $pass);
	mysql_select_db($database);

	if (isset($_GET['fold_id']) and is_numeric($_GET['fold_id']))
	{
		$fold_id = $_GET['fold_id'];
		
	}
	else $fold_id = 0;
	
	
	
	$tree = getCatTree($fold_id);
	
	$query = "SELECT pid FROM cat WHERE id=$fold_id;";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
		
	print $tree."<a href='link.php?fold_id=$row[0]'>up</a> <br />";
	
	
	$result=mysql_query("SELECT id, name FROM cat WHERE pid =".$fold_id." ORDER BY name ASC;");
	while ($row=mysql_fetch_array($result))
	{
		print "Folder: <a href='link.php?fold_id=$row[0]'>$row[1]</a> ";
		print "<a href='javascript:opener.fileBrowserReturn(\"$tree$row[1]\");window.close();'>Click</a><br />";
		
	} 
   
	$result = mysql_query("SELECT title FROM article WHERE cat_id =".$fold_id." ORDER BY title ASC;");
	while ($row=mysql_fetch_array($result))
	{
		print "Article: <a href='javascript:opener.fileBrowserReturn(\"$tree$row[0].html\");window.close();'>$row[0]</a> <br />";
	} 

function getCatTree($fold_id)
{
	$query = "SELECT name, pid FROM cat WHERE id = $fold_id;";
	$result = mysql_query($query);
	
	$row = mysql_fetch_array($result);
		
	if ($row[1] != 0)
	{
		$tree = getCatTree($row[1]).$row[0]."/";
	}
	elseif ($row[0] != false)
	{
		$tree = "/".$row[0]."/";
	}
	else
	{
		$tree ="/";
	}
	
	return $tree;

}

	
?>
