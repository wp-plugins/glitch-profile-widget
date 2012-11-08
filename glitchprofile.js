$(document).ready(function(){
	var glitchprofiles = $(".glitchprofile");
	glitchprofiles.each(function(){
		var tsid = $(this).attr('data-tsid');
		var renew = $(this).attr('data-renew');
		if (renew) {
			$.get('/wp-content/plugins/glitch-profile/get.php', {tsid: tsid}, function(data) {
				console.log('Glitch profile for '+tsid+' renewed!');
			});
		}
	});
});