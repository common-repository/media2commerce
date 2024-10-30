/*
* Media2Commerce
* Javascript helper
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
*  
*/

	function deleteType(typeIndex){

		var confirm = window.confirm("Are you sure you wish to delete this type?");

		if(confirm){
			jQuery('ul#default-types li#row-'+typeIndex).remove();	
		}		
	}

	function addType(){

		var addPosition = countTypes();
		if(addPosition<20){
			newHTML = htmlToAdd(addPosition);
			jQuery('ul#default-types').append(newHTML);	
		} else {
			alert ('Maximum number of types reached, please delete some first');
		}		
	}

	function countTypes(){
		var types = jQuery('ul#default-types li');
		return types.length;
	}

	function htmlToAdd(index){
		var newHTML = '';
		newHTML += '<li id="row-' + index + '"><input id="typecount-' + index + '" type="hidden" name="typecount-' + index + '" class="" value="0" >';
		newHTML += '<input id="type_name-' + index + '" type="text" name="type_name-' + index + '[text_string]" class = "type_name" value="Default ' + index + '" >';
		newHTML += '<input id="type_price-' + index + '" type="text" name="type_price-' + index + '[text_string]" class = "type_price" value="50" >';
		newHTML += '<input id="scaling-' + index + '" type="text" name="scaling-' + index + '[text_string]" class = "scaling" value="100" >';
		newHTML += '<input id="quality-' + index + '" type="text" name="quality-' + index + '[text_string]" class = "quality" value="100" >';
		newHTML += '<a name="delete-' + index + '" class="del-type" id="delete-' + index + '" onclick="deleteType(\''+ index + '\');">Delete Type</a></li>';
		return newHTML;
	}
