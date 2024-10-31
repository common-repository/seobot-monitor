jQuery(document).ready(function($) {
	//Check if the user's User Agent matches any of these words
	var botPattern = "googlebot|Googlebot-Mobile|Googlebot-Image|Google favicon|Mediapartners-Google";
	var re = new RegExp(botPattern, 'i');
	var userAgent = navigator.userAgent; 

	//If there is a match, it could be Google, so we load the WP action for render event
	if (re.test(userAgent)) {
		jQuery.post(
            the_ajax_script.ajaxurl,
            { 
                'action': 'seobotmonitor_event_render',
                'url' : window.location.href,
                'user_a': userAgent
            } ,
            function(response){
                console.log('yeah!');
                console.log(response);
            }
        );
	}
});