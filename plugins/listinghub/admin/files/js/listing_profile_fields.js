"use strict";
function listinghub_add_field(){	
	"use strict";
	jQuery('#custom_field_div').append('<div class="row form-group " id="field_'+dirpro.i+'"><div class=" col-sm-5"> <input type="text" class="form-control" name="meta_name[]" id="meta_name[]" value="" placeholder="Enter Post Meta Name "> </div>	<div  class=" col-sm-5"><input type="text" class="form-control" name="meta_label[]" id="meta_label[]" value="" placeholder="Enter Post Meta Label"></div><div  class=" col-sm-2"><button class="btn btn-danger btn-xs" onclick="return listinghub_remove_field('+dirpro.i+');">Delete</button>');		
	i=i+1;		
}
function listinghub_remove_field(div_id){	
	"use strict";
	jQuery("#field_"+div_id).remove();
}
function listinghub_add_menu(){	
	"use strict";
jQuery('#custom_menu_div').append('<div class="row form-group " id="menu_'+dirpro.ii+'"><div class=" col-sm-3"> <input type="text" class="form-control" name="menu_title[]" id="menu_title[]" value="" placeholder="Enter Menu Title "> </div>	<div  class=" col-sm-7"><input type="text" class="form-control" name="menu_link[]" id="menu_link[]" value="" placeholder="Enter Menu Link.  Example  www.google.com"></div><div  class=" col-sm-2"><button class="btn btn-danger btn-xs" onclick="return listinghub_remove_menu('+dirpro.ii+');">Delete</button>');
ii=ii+1;		
}
function listinghub_remove_menu(div_id){
	"use strict";
	jQuery("#menu_"+div_id).remove();
}	