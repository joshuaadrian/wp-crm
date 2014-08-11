<?php

/************************************************************************/
/* TWITTER WIDGET
/************************************************************************/

add_action( 'widgets_init', 'social_feeds_twitter_widget' );

function social_feeds_twitter_widget() {
	register_widget( 'social_feeds_twitter_widget' );
}

class social_feeds_twitter_widget extends WP_Widget {

	function social_feeds_twitter_widget() {
		$widget_ops = array( 'classname' => 'social-feeds-twitter', 'description' => __('A widget that displays your Twitter feed', 'social_feeds_twitter') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'social-feeds-twitter-widget' );
		
		$this->WP_Widget( 'social-feeds-twitter-widget', __('Twitter Widget', 'social_feeds_twitter'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {

		extract( $args );
		$count = isset($instance['count']) && !empty($instance['count']) ? $instance['count'] : '';
		echo $before_widget;
		echo do_shortcode('[twitter_feed count='. $count . ']');
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['count'] = strip_tags( $new_instance['count'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'count' => __('4', 'social-feeds-twitter') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Count:', 'social-feeds-twitter'); ?></label>
			<input type="number" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" style="width:100%;" value="<?php echo $instance['count']; ?>" />
		</p>
	<?php
	}
}

/************************************************************************/
/* INSTAGRAM WIDGET
/************************************************************************/

add_action( 'widgets_init', 'social_feeds_instagram_widget' );

function social_feeds_instagram_widget() {
	register_widget( 'social_feeds_instagram_widget' );
}

class social_feeds_instagram_widget extends WP_Widget {

	function social_feeds_instagram_widget() {
		$widget_ops = array( 'classname' => 'social-feeds-instagram', 'description' => __('A widget that displays your Instagram feed', 'social_feeds_instagram') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'social-feeds-instagram-widget' );
		
		$this->WP_Widget( 'social-feeds-instagram-widget', __('Instagram Widget', 'social_feeds_instagram'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {

		extract( $args );
		$count = isset($instance['count']) && !empty($instance['count']) ? $instance['count'] : '';
		echo $before_widget;
		echo do_shortcode('[instagram_feed count='. $count . ']');
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['count'] = strip_tags( $new_instance['count'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'count' => __('8', 'social-feeds-instagram') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Count:', 'social-feeds-instagram'); ?></label>
			<input type="number" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" style="width:100%;" value="<?php echo $instance['count']; ?>" />
		</p>
	<?php
	}
}

/************************************************************************/
/* PINTEREST WIDGET
/************************************************************************/

add_action( 'widgets_init', 'social_feeds_pinterest_widget' );

function social_feeds_pinterest_widget() {
	register_widget( 'social_feeds_pinterest_widget' );
}

class social_feeds_pinterest_widget extends WP_Widget {

	function social_feeds_pinterest_widget() {
		$widget_ops = array( 'classname' => 'social-feeds-pinterest', 'description' => __('A widget that displays your Pinterest feed', 'social_feeds_pinterest') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'social-feeds-pinterest-widget' );
		
		$this->WP_Widget( 'social-feeds-pinterest-widget', __('Pinterest Widget', 'social_feeds_pinterest'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {

		extract( $args );
		$content_url = isset($instance['content_url']) && !empty($instance['content_url']) ? $instance['content_url'] : '';
		echo $before_widget;
		echo do_shortcode('[pinterest_feed content='. $content_url . ']');
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['content_url'] = strip_tags( $new_instance['content_url'] );
		return $instance;
	}

	function form( $instance ) {
		global $social_feeds_options;

		if ( isset( $social_feeds_options['pinterest_content'] ) ) {
			$content = $social_feeds_options['pinterest_content'];
		} else {
			$content = 'http://www.pinterest.com/pinterest/';
		}

		$defaults = array( 'content_url' => __( $content, 'social-feeds-pinterest') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'content_url' ); ?>"><?php _e('Content URL:', 'social-feeds-pinterest'); ?></label>
			<input type="type" id="<?php echo $this->get_field_id( 'content_url' ); ?>" name="<?php echo $this->get_field_name( 'content_url' ); ?>" style="width:100%;" value="<?php echo $instance['content_url']; ?>" />
		</p>
	<?php
	}
}

?>