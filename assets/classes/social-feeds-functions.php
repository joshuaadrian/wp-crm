<?php

/************************************************************************/
/* SET CUSTOM EVENT SCHEDULES
/************************************************************************/

add_filter('cron_schedules', 'sf_cron_schedules');

function sf_cron_schedules( $schedules ) {

	if ( !array_key_exists('every_five_minutes', $schedules) ) {
		$schedules['every_five_minutes'] = array(
			'interval' => 60 * 5,
			'display'  => __( 'Every five minutes' )
		);
	}

	if ( !array_key_exists('every_fifteen_minutes', $schedules) ) {
		$schedules['every_fifteen_minutes'] = array(
			'interval' => 60 * 15,
			'display'  => __( 'Every fifteen minutes' )
		);
	}

	if ( !array_key_exists('every_half_hour', $schedules) ) {
		$schedules['every_half_hour'] = array(
			'interval' => 60 * 30,
			'display'  => __( 'Every half hour' )
		);
	}

	return $schedules;

}

/************************************************************************/
/* SET EVENT SCHEDULE
/************************************************************************/

add_action('sf_social_event', 'sf_social_calls');

function sf_social_activation() {
	if ( !wp_next_scheduled('sf_social_event') ) {
		wp_schedule_event( current_time('timestamp'), 'every_fifteen_minutes', 'sf_social_event' );
	}
}

add_action('wp', 'sf_social_activation');

/************************************************************************/
/* CLEAN EMOJI FROM TWITTER FEED
/************************************************************************/
function removeTwitterEmoji( $tweets ) {

  $cleaned_tweets = $tweets;

  foreach ( $tweets as $tweet_key => $tweet ) {
    
    $cleaned_tweets[$tweet_key]['text'] = removeEmoji( $tweet['text'] );

    if ( isset( $tweet['retweeted_status']['text'] ) ) {
      $cleaned_tweets[$tweet_key]['retweeted_status']['text'] = removeEmoji( $tweet['retweeted_status']['text'] );
    }

  }

  return $cleaned_tweets;

}

/************************************************************************/
/* CLEAN EMOJI FROM INSTAGRAM FEED
/************************************************************************/
function removeInstagramEmoji( $instagrams ) {

	$cleaned_instagrams = $instagrams;

	foreach ($instagrams as $instagram_key => $instagram) {
		
		if ( $instagram['comments']['count'] > 0 ) {

			foreach ( $instagram['comments']['data'] as $instagram_comment_key => $instagram_comment_value ) {

		    $cleaned_instagrams[$instagram_key]['comments']['data'][$instagram_comment_key]['text'] = removeEmoji( $instagram_comment_value['text'] );
		    $cleaned_instagrams[$instagram_key]['comments']['data'][$instagram_comment_key]['from']['full_name'] = removeEmoji( $instagram_comment_value['from']['full_name'] );
		    $cleaned_instagrams[$instagram_key]['comments']['data'][$instagram_comment_key]['from']['username'] = removeEmoji( $instagram_comment_value['from']['username'] );
		    
			}

		}

    if ( $instagram['likes']['count'] > 0 ) {

      foreach ( $instagram['likes']['data'] as $instagram_like_key => $instagram_like_value ) {

        $cleaned_instagrams[$instagram_key]['likes']['data'][$instagram_like_key]['username'] = removeEmoji( $instagram_like_value['username'] );
        $cleaned_instagrams[$instagram_key]['likes']['data'][$instagram_like_key]['full_name'] = removeEmoji( $instagram_like_value['full_name'] );
        
      }

    }

	}

  return $cleaned_instagrams;

}

function removeEmoji( $text ) {

	// Match Emoticons
  // $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
  // $clean_text = preg_replace($regexEmoticons, '', $text);

  // // Match Miscellaneous Symbols and Pictographs
  // $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
  // $clean_text = preg_replace($regexSymbols, '', $clean_text);

  // // Match Transport And Map Symbols
  // $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
  // $clean_text = preg_replace($regexTransport, '', $clean_text);

  $clean_text = preg_replace('/[^0-9a-zA-Z\!\@\#\$\%\^\&\*\(\)\-\/_:. ]/', '', $text);

  return $clean_text;

}

/************************************************************************/
/* ENABLE SHORTCODES FOR TEXT WIDGETS
/************************************************************************/

add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);

/************************************************************************/
/* TWITTER FUNCTIONS
/************************************************************************/

function linkify_twitter_status( $status_text ) {
  // linkify URLs
  $status_text = preg_replace(
    '/(https?:\/\/\S+)/',
    '<a href="\1" target="_blank">\1</a>',
    $status_text
  );
 
  // linkify twitter users
  $status_text = preg_replace(
    '/(^|\s)@(\w+)/',
    '\1@<a href="http://twitter.com/\2" target="_blank">\2</a>',
    $status_text
  );
 
  // linkify tags
  $status_text = preg_replace(
    '/(^|\s)#(\w+)/',
    '\1#<a href="http://search.twitter.com/search?q=%23\2" target="_blank">\2</a>',
    $status_text
  );
 
  return $status_text;
  
}

function twitter_relative_time( $time ) {

  $tweet_time = strtotime( $time );
  $delta      = time() - $tweet_time;

  if ( $delta < 60 ) {
    return 'Less than a minute ago';
  } elseif ( $delta > 60 && $delta < 120 ) {
    return 'About a minute ago';
  } elseif ( $delta > 120 && $delta < ( 60 * 60 ) ) {
    return strval(round(($delta/60),0)) . ' minutes ago';
  } elseif ( $delta > ( 60 * 60 ) && $delta < ( 120 * 60 ) ) {
    return 'About an hour ago';
  } elseif ( $delta > ( 120 * 60 ) && $delta < ( 24 * 60 * 60 ) ) {
    return strval( round( ( $delta / 3600 ), 0 ) ) . ' hours ago';
  } else {
    return date( 'F y g:i a', $tweet_time );
  }

};

/************************************************************************/
/* EMBED PINTEREST SCRIPT
/************************************************************************/
$social_feeds_shortcode_pinterest = false;

function social_feeds_print_my_script() {

	global $social_feeds_shortcode_pinterest;

	if ( ! $social_feeds_shortcode_pinterest )
		return;

	wp_print_scripts('social-feeds-pinterest');
}

add_action('wp_footer', 'social_feeds_print_my_script');

/************************************************************************/
/* AJAX GET FEEDS
/************************************************************************/
function sf_get_feeds_callback() {

  global $wpdb, $social_feeds_options;

  //echo json_encode( array('this'=>'that') ); die();

  $output = array(
    'twitter'   => false,
    'instagram' => false
  );

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
    $social_feeds_options['twitter_cache'] = isset( $twitter_data['errors'] ) ? removeTwitterEmoji( json_decode( $twitter_data, true, 10 ) ) : false;
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

    $output['instagram'] = isset( $instagrams['meta']['code'] ) && $instagrams['meta']['code'] == 200 ? true : false;

  }

  update_option( 'sf_options', $social_feeds_options );

  echo json_encode( $output ); die();

}

add_action('wp_ajax_sf_get_feeds', 'sf_get_feeds_callback');