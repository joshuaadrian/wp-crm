<?php

/************************************************************************/
/* SOCIAL API CALLS
/************************************************************************/

function sf_social_calls() {

	// GET PLUGIN OPTIONS
	global $social_feeds_options;

	if ( !isset($social_feeds_options['twitter_cache']) ) :
		$social_feeds_options['twitter_cache'] = '';
	endif;
	if ( !isset($social_feeds_options['twitter_error_log']) ) :
		$social_feeds_options['twitter_error_log'] = '';
	endif;
	if ( !isset($social_feeds_options['instagram_cache']) ) :
		$social_feeds_options['instagram_cache'] = '';
	endif;
	if ( !isset($social_feeds_options['instagram_error_log']) ) :
		$social_feeds_options['instagram_error_log'] = '';
	endif;

	// CHECK FOR TWITTER INFO
	if ( isset( $social_feeds_options['twitter_username'] ) && isset( $social_feeds_options['twitter_oauth_access_token'] ) && isset( $social_feeds_options['twitter_oauth_access_token_secret'] ) && isset( $social_feeds_options['twitter_consumer_key'] ) && isset( $social_feeds_options['twitter_consumer_secret'] ) ) {

		// TWITTER VARIABLES
		$twitter_username         = $social_feeds_options['twitter_username'];
		$twitter_post_count       = 20;
		$twitter_include_rts      = isset( $social_feeds_options['twitter_include_rts'] ) && $social_feeds_options['twitter_include_rts'] ? 'true' : 'false';
		$twitter_include_replies  = isset( $social_feeds_options['twitter_include_replies'] ) && $social_feeds_options['twitter_include_replies'] ? 'true' : 'false';
		$twitter_include_entities = isset( $social_feeds_options['twitter_include_entities'] ) && $social_feeds_options['twitter_include_entities'] ? 'true' : 'false';

		// REQUIRE TWITTER API PHP LIBRARY
		require_once(SF_PATH . 'assets/libs/twitter/twitter_exchange.php');

		// SET ACCESS TOKENS HERE - see: https://dev.twitter.com/apps/
		$settings = array(
			'oauth_access_token'        => $social_feeds_options['twitter_oauth_access_token'],
			'oauth_access_token_secret' => $social_feeds_options['twitter_oauth_access_token_secret'],
			'consumer_key'              => $social_feeds_options['twitter_consumer_key'],
			'consumer_secret'           => $social_feeds_options['twitter_consumer_secret']
		);
		
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?screen_name='. $twitter_username .'&count='. $twitter_post_count;
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($settings);
		$twitter_data = $twitter->setGetfield($getfield)
		             			->buildOauth($url, $requestMethod)
		            			->performRequest();
		            			
		// UPDATE SF OPTION WITH RETURNED TWITTER DATA
		$social_feeds_options['twitter_cache'] = removeTwitterEmoji( json_decode( $twitter_data, true, 10 ) );
		$social_feeds_options['twitter_log']   = date("F j, Y, g:i a") . ' twitter success | rest call url =>  ' . $url . "\r\n\n";

		$output['twitter'] = $social_feeds_options['twitter_cache'] ? true : false;
	    
	}

	// CHECK FOR INSTAGRAM INFO
	if ( isset( $social_feeds_options['instagram_user_id'] ) ) {

		// INSTAGRAM VARIABLES
		$instagram_access_token  = $social_feeds_options['instagram_access_token'];
		$instagram_user_id       = $social_feeds_options['instagram_user_id'];
		$instagram_count         = 20;

		// INSTAGRAM CURL CALL
	    $instagram_url = 'https://api.instagram.com/v1/users/'.$instagram_user_id.'/media/recent/?access_token='. $instagram_access_token.'&count='. $instagram_count;
	    $instagram_ch = curl_init();
	    curl_setopt ($instagram_ch, CURLOPT_URL, $instagram_url);
	    curl_setopt ($instagram_ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt ($instagram_ch, CURLOPT_TIMEOUT, 20);
	    curl_setopt ($instagram_ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	    curl_setopt ($instagram_ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt ($instagram_ch, CURLOPT_RETURNTRANSFER, 1); 
	    $instagram_data = curl_exec($instagram_ch);
	    $instagram_error = curl_errno($instagram_ch);
	    curl_close($instagram_ch);
	    $instagrams = json_decode( $instagram_data, true, 20 );
	    //_log($instagrams['data'][1]);
	    // CHECK FOR ERRORS AND WRITE JSON TO FILE
	    if ( $instagrams['meta']['code'] == 200 ) {
	    	$social_feeds_options['instagram_cache'] = removeInstagramEmoji( $instagrams['data'] );
	    	$social_feeds_options['instagram_log'] = date("F j, Y, g:i a") . ' instagram success | rest call url =>  ' . $instagram_url . "\r\n\n";
	    } else {
			$social_feeds_options['instagram_error_log'] = date("F j, Y, g:i a") . ' instagram error json => ' . $instagram_data . ' | rest call url =>  ' . $instagram_url . "\r\n\n";
		}

	}

	update_option( 'sf_options', $social_feeds_options );

}

?>