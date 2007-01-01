<?php

/**
 *Class for creating a frontend document
 *
 *This class is called by the index.php and generates the appropriate document for requested URL
 *
 *@author		Kai Hoewelmeyer <kai-h@gmx.de>
 *@package		Codename: mr. magister
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
class view
{
	private
		$uri = array(),
		$link_identifier,
		$output = '',
		$article = false,
		$cat = false;
		
	/**
	 *Constructor assigns given parameters to class variables
	 *
	 *@param		array $uri An array containing the requested URL exploded by the slashes
	 *@param		resource $link current link to mysql database	 	 	 
	 *@access	public
	 */	 	 	 	
	public function __construct($uri, $link)
	{
		$this->link_identifier = $link;
		$this->uri = $uri;
	}
	
	/**
	 *Generates the general output depending on given URL array
	 *
	 *@access	public
	 */	 	 	 	
	public function generate()
	{		
	
	//is this a request for the homepage?
		if ($this->uri[0]=='')
		{
			$this->output="Hello";
		}
	//if not, it has to be a request for an article or category
		else
		{
		//how many parts incoparates the url?			
			$count = count($this->uri);
		
		//if html is part of the last element of the uri, it's an article	
			if(strpos($this->uri[$count-1],".html"))
			{
			//define the article's name in a variable and cut it off the url and the .html	
				$this->article = substr(array_pop($this->uri),0,-5);
				
				
			//renew the count	
				$count = count($this->uri);
			}
			
			//acquires the consecutive number of the category
			$this->cat = $this->get_cat_id($this->uri, $count - 1);
			
			
			
		//if the category number is found, check if it es an article or a category overview
		//it has to be the !== operator, because we need to differentiate between 0 and false!
			if($this->cat!==false)
			{			
				if($this->article!=false)
				{
					$this->article();
				}
				else
				{
					$this->category();	
				}
			}
			else //if none applies, generate a 404
			{
				$this->err404();
			}
		}
	
	
	}
	
	
	/**
	 *This method tries to get the id for the current category.
	 *
	 *@param		array $uri requested URL exploded by slashes
	 *@param		int $pos current position in $uri, used as abort condition in this recursive method	 	 	 
	 *@access	private
	 *
	 */	 	 	 	
	private function get_cat_id($uri, $pos)
	{
	
	//if it is a top-ranked category, just spit out the number
		if($pos == 0)
		{
			$query = "SELECT id, pid, name FROM cat WHERE name='$uri[$pos]' AND pid=0;";
		}
	//if it is an article without a category it is cat_number 0
		elseif($pos == -1){ return 0;}
	//otherwise try to find the category regardless of where it is ranked
		else
		{
			$query = "SELECT id, pid, name FROM cat WHERE name='$uri[$pos]';";
		}
		
		$result = mysql_query($query, $this->link_identifier);
		
	//if the result is unique, return the cat_number!
		if(mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_row($result);
			return $row[0];			
		}
	//if it is not unique, find the parent number in order to make it unique.
		elseif(mysql_num_rows($result) > 1)
		{
			$parent_id = $this->get_cat_id($uri, $pos - 1);
			
			if($parent_id != false) 
			{
				$result = mysql_query("SELECT id, pid, name FROM cat WHERE name='$uri[$pos]' AND pid=$parent_id;", $this->link_identifier);
				$row = mysql_fetch_row($result);
				return $row[0];
			}
		}
		else //if all attempts fail, just return a "404"!
		{
			return false;
		}
	}
	
	/**
	 *Handles the output for an article with db querying and template parsing
	 *
	 *@access	private
	 */	 	 	 	
	private function article()
	{				
	
	
		$query = "SELECT title, synopsis, text, author, lastupdated, keyword ";
		$query .= "FROM article WHERE title='$this->article' AND cat_id=$this->cat";
		
		$result = mysql_query($query,$this->link_identifier);
		
		if(mysql_num_rows($result)==1)
		{
		
		//load template class
			include_once("parser/templ_parser.php");
			
		//instatiate a new object
			$parser = new templ_parser("article.html");
			
			$placeholder = mysql_fetch_assoc($result);
			
			//set timezone to gmt
			putenv("TZ=Europe/Berlin");			
			$placeholder["lastupdated"] = date("d.m.y", $placeholder["lastupdated"]);
			
			$this->output= $parser->parse($placeholder);
		
		}		
		
		
		
	
	}
	/**
	 *Handles the case of an 404 Error
	 *
	 *@access	private	 
	 */	 	 	
	private function err404()
	{
		$this->output="404";
	}
	
	/**
	 *Handles the request for a specific category
	 *
	 *@access	private	 
	 */	 	 	
	private function category()
	{
		$this->output=$this->cat;
	}
	
	/**
	 *Returns the private class variable $output
	 *
	 *@return	string the generated output
	 *@access	public	 	 	 
	 */	 	
	public function output()
	{
		return $this->output;
	}


}
