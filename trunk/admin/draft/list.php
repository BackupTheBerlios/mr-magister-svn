<?php

	require_once("../../include/include.php");
	
	mysql_connect($server, $user, $pass);
	
	mysql_select_db($database);
	
	$query = "SELECT id,title,synopsis from article;";
	
	$test = "<strong>test</strong>";
	echo(strip_tags($test));
	
	$result = mysql_query($query);
	
	echo("<table border=1><th><td>id</td><td>title</td><td>synopsis</td></th>");
	
	while($row=mysql_fetch_array($result))
	{
		echo("<tr><td></td><td>".$row[0]."</td><td><a href='edit.php?id=$row[0]'>".$row[1]."</a></td><td>".strip_tags($row[2])."</td></tr>");
	}
	
	

?>
