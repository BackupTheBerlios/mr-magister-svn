<?php

/**
 *	Class containing methods for a basic search
 *	
 *This class handles basic search requests and their output. 
 *In future they will be able to handle internal and administrative search request aswell.
 *
 *@author		Kai Höwelmeyer <kai-h@gmx.de>
 *@package		Codename: mr.magister
 *@version		1.0 
 * 
 *@if license
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
 */     
class search
{
		 	
	private
	$link_identifier = '',
	$searchstring = '',
	$searcharray = '',
	$output = '';

	/**
	 *Constructor
	 *
	 *Examines the given parameter and assigns it to its relative class variable.
	 *Furthermore the query string given by the superglobal $_GET is assigned to its corresponding class variable 	 
	 *
	 *@param		resource $link_identifier represents the currently openend connection to the mysql database
	 *@access	public
	 *	 	  	 
	 */	 	
	public function __construct($link_identifier)
	{
		$this->link_identifier = $link_identifier;
		$this->searchstring = stripslashes(mysql_real_escape_string($_GET['query']));
		
	}

	/**
	 *Performs the search and does not need any parameters as they are already initialized
	 *in the constructor. The template parsed output is assigned to
	 *the private class variable $output. For output use method output()
	 *
	 *@access		public	 	  
	 */	 	
	public function perform_search()
	{
	//split query words in array
	$this->searcharray = preg_split('/[ +\"\x2D*~]+/', $this->searchstring); 
	
	//walk array with auto_sw_replace
	foreach ($this->searcharray as $value)
	{
		if(strlen($value) < 4 and strlen($value) > 0)
		{
			$this->auto_sw_replace();
			break;
		}
	}

	//formulate main query to database
	$query = "SELECT id, text, match text against('$this->searchstring') as relevance FROM article ";
	$query .= "WHERE MATCH text AGAINST('$this->searchstring' IN BOOLEAN MODE) order by relevance desc;";

	//send query to database
	$result = mysql_query($query, $this->link_identifier);

	//compile template for appropriate case and parse it with the according values
	include_once("parser/templ_parser.php");

	switch (mysql_num_rows($result))
	{
		case 0:
			{
				$parser = new templ_parser("search.html");

				if (strlen($this->searchstring) > 3)
				{
					$parser->compile_templ("if none");
				}
				else
				{
					$parser->compile_templ("if too short");
				}

				$this->output = $parser->parse();

			}
			break;

		default:
			{
			//init templ_parser
			$parser = new templ_parser("searchresults.html");

			//format the results
			while ($row = mysql_fetch_assoc($result))
			{
			//strp the html and maybe php tags
				$row['text'] = strip_tags($row['text']);

			//center the searched words in the abstract
				$row['text'] = $this->center_abstract($row['text']);

			//bold the searchwords
				$row['text'] = $this->bold_it($row['text']);
				
			//generate the link to searchresult
				include("include/tools.php");
				$row['URL'] = tools::generateURL($row['id'], NULL,$this->link_identifier);

			//calculate the MySQL Relevance in percent
				$row['relevance'] = $this->rel_to_per($row['relevance']);

			//put the formatted results in an output variable
				$searchresults.= $parser->parse($row);
			}

		//destroy templ_parser and create new one
			$parser = new templ_parser("search.html");

		//compile the template for the case of searchresults
			$parser->compile_templ("if searchresults");

		//parse template
			$placeholders = array("searchresults" => $searchresults);
			$this->output = $parser->parse($placeholders);

			}
			break;
	}


	}

	/**
	 *In case of words that are too  short to be processed this function examines if the word is maybe a shortform and tries to replace it with its corresponding long form. For this purpose all short forms have to be placed inside a mysql table called sw_repl. See database structure.
	 *
	 *@access	private	 	 
	 */	 	
	private function auto_sw_replace()
	{

		//form the query to the databse
		$query = "SELECT to_repl, with_word FROM sw_repl;";

		$result = mysql_query($query, $this->link_identifier) or die(mysql_error());

		while ($row = mysql_fetch_row($result))
		{
		//main add the word functioncall
			$this->searchstring = preg_replace("/\b(".$row[1].")/i", " \${1} ".$row[2], $this->searchstring);
		}

	}

	/**
	 *This function cut out a section of the text which contains at least one of the search words and tries to center this word in the newly created abstract.
	 *
	 *@access	private
	 *@param		string Should contain the abstract that needs to be centered
	 *@return	string Returns the centered string	 	 	 	 
	 */	 	
	private function center_abstract($abstract) //used to center the word that was sought for in the abstract
	{
	//seek for an apropriate searchword in abstract
		foreach($this->searcharray as $value)
		{
			$pos = stripos($abstract, $value);
			if($pos!=false)break;
		}

	//in case of position below 60 reset it to 60
		if($pos <= 60)$pos = 60;

	//center the word in the abstract and shorten the abstract
		$anfpos = strpos($abstract, " ", $pos-60);
		$endpos = strpos($abstract, " ", $pos+60);
		$abstract = substr($abstract, $anfpos, ($endpos - $anfpos));

		return $abstract;
	}

	/**
	 *Puts all the words that are in the abstract and are searched for in bold.
	 *
	 *@access	private
	 *@param		string The abstract containing the words which need to be put in bold
	 *@return	string the abstract with the words in bold	 	 	 	 
	 *
	 */	 	 	
	private function bold_it($abstract) //bold all in abstract contained words
	{
		foreach($this->searcharray as $value)
		{
			if($value == '')continue;

			$offset=0;

		//how many words have to be bolded
			$boldanz = substr_count(strtolower($abstract), strtolower($value));

		//do the bold for each occurence of the word
			for($i=0;$i < $boldanz;$i++)
			{
				$boldanfpos=strpos(strtolower($abstract), strtolower($value), $offset);
				$abstract = substr($abstract, 0, $boldanfpos)."<b>".substr($abstract, $boldanfpos, strlen($value))."</b>".substr($abstract, $boldanfpos + strlen($value), strlen($abstract));
				$offset = $boldanfpos + strlen($value);
			}
		}

		return $abstract;

	}

	/**
	 *The relevance value that is returned by mysql is not appropriate for displaying it. Therefore this method does some calculating to convert the given relavancy into a per cent value.
	 *The calculation algorithm was found by trial and error.
	 *
	 *@access	private
	 *@param		float $relevance the relevance value provided by mysql
	 *@return	string converted value with a trailing %	 	 	 	 	 	 
	 *
	 */	 	 	
	private function rel_to_per($relevance) //function for converting the MySQL relevance to an appropriate percent value
	{
		$swcount = count($this->searcharray);

		if ($relevance > ($swcount + 1))
		{
			$percent = "100 %";
		}
		else
		{
			$percent = ($percent==0) ? 1 : $percent; //korrigieren!!
			$percent = round((($relevance /  ($swcount + 1) * 100)),1)."%";
		}
		return $percent ;
	}

	/**
	 *Simple method to return the generated output to parent resp. caller.
	 *
	 *@access	public
	 *@return	string generated output	 	 	 
	 *
	 */	 	 	
	public function output()
	{
		return $this->output;
	}

}



?>

