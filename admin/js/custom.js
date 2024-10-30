jQuery(document).ready(function(){
	jQuery("a.my-tool-tip").tooltip();

	jQuery("#accsubmit").click( function() {
			if(jQuery('#accountidupdate').val() == ""){
				jQuery('#accountidupdate').css("border-bottom", "2px solid red");
				return false;
			}
		 	var accountid = jQuery('#accountidupdate').val();
		    var post_type = jQuery('#post_type').val();
		    jQuery.ajax({
		        url: ajaxurl,type: 'POST', dataType: "JSON",
		        data: {
		            'action':'wpconvurt_update_account_key',
		            'accountid' : accountid,
		            'post_type' : post_type
		        },
		        beforeSend: function()
    			{
			        jQuery('.loading').show();
			    },
		        success:function(data) {
		            // This outputs the result of the ajax request
		            jQuery('.loading').hide();
		            if(data.flag == 0){
		            	jQuery('.status').html(data.msg).removeClass('alert-success').addClass(data.alertclass);
		            }if(data.flag == 1){
		            	jQuery('.status').html(data.msg).removeClass('alert-danger').addClass(data.alertclass);
		            	setTimeout(function(){ 
		            	 	jQuery('#accountid').val(data.accountid);
		            	 	jQuery('#accountidupdate').val(data.accountid);
		            		jQuery('#myModalNorm').modal('toggle'); 
		            	}, 2000);
		            }
		        },
		        error: function(errorThrown){
		            console.log(errorThrown);
		        }
		    }); 
		 	return false;
	}); //status alert alert-danger
	jQuery(document).on("click", ".wpconvurt_edit_account_key", function(){
		jQuery('.status').removeClass('alert alert-danger');
		jQuery('.status').empty();
	});

});