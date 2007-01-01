<?php
/**
 *Main script / Floor script
 *
 *This script handles ALL requests. Thanks to the mod_rewrite directive of apache all requests except those for pictures, javascripts, css and other php files are redirected to this script. It decides which classes are to be instantiated and handles the final output. Thus it functions the same way as an output buffer.
 *
 *@author		Kai Hoewelmeyer <kai-h@gmx.de>
 *@package		Codename: mr. magister
 *@version		1.0
 *
 * @if license
 *  	 
 *  Mr. Magister -CMS (c) 2006 by Kai Hoewelmeyer <kai-h@gmx.de>
 *  
 *  License in LICENSE.txt
 *  
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.  
 * @endif
 **/    

//decode the request uri and explode in an array
	$uri = urldecode($_SERVER['REQUEST_URI']);
	
	$pos = strpos($uri, "?");
	if ($pos !== false) $uri = substr($uri, 0, $pos);
	$uri = trim($uri, "/");
	
	$uri_array=explode("/", $uri);


//include configuration data
	require_once "include/include.php";

//if the file exist physically, open it as long as it is a file not a directory
	if(file_exists(BASEDIR.$uri) and strpos(array_pop($uri_array),".")!==FALSE)
	{
	include(BASEDIR.$uri);
	exit();
	}

//open link to database server and select right database
	$link_identifier = mysql_connect(SERVER, USER, PASS) or die("Keine Verbindung zur Datenbank!!!!");

	mysql_select_db(DATABASE, $link_identifier); 


//check, what's the request for...
	switch($uri_array[0])
	{
	case "suche":
		require_once("search/search.php");
		
		if (isset($_GET))
		{
			$module = new search($link_identifier);
			
			$module->perform_search();
		}	
	break;
	default:
		require_once("articles/view.php");
		
		$module = new view($uri_array, $link_identifier);
		
		$module->generate();
	break;
	}

//parse the navigational contents
	include_once("parser/templ_parser.php");
	$parser = new templ_parser("nav.html");
	$nav = $parser->parse();

//main parse, include of content	
	$parser= new templ_parser("main.html");
	$output = $parser->parse(array("navigation" => $nav, "content" => $module->output()));

//print to page	
	print $output;	


	
?>
