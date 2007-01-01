<?php

/**
 *	Template Parser
 *	
 *A simple template parser with capabilities for "Conditional Templates": A template written for multiple cases. The template can be compiled according to the current case.
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
class templ_parser
{
	private
		$template,
		$output;
		
	/**
	 *Constructor
	 *
	 *Loads the template in the class variable $template
	 *
	 *@params	string filename of the template. If the template is not located within the standard template folder as defined by TEMPLATE_PATH in the configuration, you have to include the path	 	 	 	 
	 *@access	public
	 */	 	 	
	public function __construct($template)
	{
		if(file_exists($template))
		{
			$this->template = file_get_contents($template);
		}
		elseif(file_exists(TEMPLATE_PATH.$template))
		{
			$this->template = file_get_contents(TEMPLATE_PATH.$template);
		}
		else
		{
			die("Template $template konnte nicht gefunden werden!");
		}
	
	}
	
	/**
	 *This is the main function. It replaces the placeholders in the template with the appropriate values of the associative array in $placeholders.
	 *
	 *@access	public
	 *@param		array $placeholder Associative array of the placeholders. Key is the name of the placeholder and value is the value of the placeholder
	 *@return	string template with placeholders replaced by its values	 	 	 	  
	 *
	 */	 	 	
	public function parse($placeholders="")
	{
		
		$this->output = $this->template;
		
		if($placeholders != "")
		{
			foreach($placeholders as $placeholder => $value)
			{
				
				$this->output = preg_replace("/\{$placeholder\}/i", $value, $this->output);
			}
		}
		
		return $this->output;
		
	}
	
	/**
	 *This is the function for handling "Conditional Templates". Just input the case in the parameter $case and this function will compile the template according to the given state/case.
	 *
	 *@access	public
	 *@param		string according to this state will the template be compiled	 	 	 
	 *
	 *
	 */	 	 	 	
	function compile_templ($case="")
	{
		$needle = "[".$case."]";
		
		$pos = strpos($this->template, $needle);
		
		$endpos = strpos($this->template, "[", $pos+1);
		$endpos = $endpos == "" ? strlen($this->template):$endpos; 
		
		$len_needle = strlen($needle);
		
		$this->template = substr($this->template, $pos + $len_needle, $endpos - ($pos + $len_needle));
	}
	
}	
