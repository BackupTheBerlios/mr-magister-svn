<?php
/**
 *@brief 	Tools Library
 *
 *This is a collection of tools that are used within various classes
 *Library! Thus do not instantiate.  
 *
 *@author 	Kai Höwelmeyer <kai-h@mx.de>
 *@package	mr. magister
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
 *	 	 	 	 	 
 *
 *This is a collection of tools that are used within various classes
 *Library! Thus do not instantiate.
 */
		  	 
	class tools
	{
		/**
		*This function generates an absolute URL for given article or category.
		*Only $article_id OR $cat_id should be provided!
		*This method functions as a wrapper for getCatTree.
		*
		*@param		int $article_id
		*@param		int $cat_id
		*@param		resource current valid link to database
		*@return	string absolute URL for provided article or category
		*
		*/
		public function generateURL($article_id, $cat_id, $link_identifier)
		{
			if (isset($article_id))
			{
				$query = "SELECT cat_id, title FROM article WHERE id = $article_id;";
				$result = mysql_query($query, $link_identifier);
				
				$row = mysql_fetch_array($result);
				
				$cat_id = $row[0];
				
				$tree = self::getCatTree($cat_id, $link_identifier);
				
				$URL = $tree.$row[1].".html";
				
				return $URL;
			}
			elseif (isset($cat_id))
			{
				$URL = getCatTree($cat_id, $link_indentifier);
				
				return $URL;
			}
			else return false;				
		}
		
		/**
		 *This function generates the folder structure for given cat_id.
		 *It's called by generate_URL and therefore only defined private.
		 *
		 *@access	private
		 *@param		int $cat_id conclusive category number
		 *@param		resource $link_identifier a valid link to mysql database
		 *@return	string contains the tree for $cat_id
		 *
		 */		 		 		 		
		private function getCatTree($cat_id, $link_identifier)
		{
			$query = "SELECT name, pid FROM cat WHERE id = $cat_id;";
			$result = mysql_query($query, $link_identifier);
						
			$row = mysql_fetch_array($result);
				
			if ($row[1] != 0)
			{
				$tree = self::getCatTree($row[1],$link_identifier).$row[0]."/";
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
	
	}	 	 	 	
?>
