jQuery(document).ready(function($)
{
	//Listener for removing slides click
	$('#removeSlidesButton').on( 'click', function (  ){
		$( "#adminSlidesUploadDiv" ).show( "fast");
		$( "#adminSlidesLinkDiv" ).hide( "fast");
		$( "#adminSlidesRestoreDiv" ).show( "fast");
		
		// Set the delete slides input value to true
		
		$('#deleteSlides').val(true);
			
	});
	
	$('#adminSlidesRestoreDiv').on( 'click', function (  ){
		$( "#adminSlidesUploadDiv" ).hide( "fast");
		$( "#adminSlidesLinkDiv" ).show( "fast");
		$( "#adminSlidesRestoreDiv" ).hide( "fast");
		$('#deleteSlides').val(false);
			
	});
	
});