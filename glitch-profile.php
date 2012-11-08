<?php
/*
Plugin Name: Glitch Profile
Plugin URI: http://labs.gerbenjacobs.nl/glitchprofile/
Description: Allows you to add a widget to your site that contains information about a Glitch
Author: Gerben Jacobs
Version: 1
Author URI: http://www.gerbenjacobs.nl
*/


class GlitchProfileWidget extends WP_Widget {
	function GlitchProfileWidget() {
		$widget_ops = array(
			'classname' => 'GlitchProfileWidget', 
			'description' => 'Allows you to add a widget to your site that contains information about a Glitch'
		);
		$this->WP_Widget('GlitchProfileWidget', 'Glitch Profile Widget', $widget_ops);
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'My Glitch', 'glitch_id' => 'PHFRDC7RL4D2N6Q' ) );
		$title = $instance['title'];
		$glitch_id = $instance['glitch_id'];
		echo '
			<p>
				<label for="'.$this->get_field_id('title').'">Title (Optional)</label>
				<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.attribute_escape($title).'" />
			</p>
			<p>
				<label for="'.$this->get_field_id('glitch_id').'">Glitch ID</label>
				<input class="widefat" id="'.$this->get_field_id('glitch_id').'" name="'.$this->get_field_name('glitch_id').'" type="text" value="'.attribute_escape($glitch_id).'" />
			</p>
		';
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['glitch_id'] = $new_instance['glitch_id'];
		return $instance;
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		wp_enqueue_script('jquery');
		wp_enqueue_script('glitchprofile-js', plugin_dir_url( __FILE__ ).'glitchprofile.js', array('jquery'));
		wp_enqueue_style('glitchprofile-css', plugin_dir_url( __FILE__ ).'glitchprofile.css');
		
		// Get data
		$cache_url = sprintf('%scache/%s.js', plugin_dir_path( __FILE__ ), $instance['glitch_id']);
		$renew_cache = false;
		if (!file_exists($cache_url)) {
			// First time ever, get a copy
			$glitch_url = sprintf('http://api.glitch.com/simple/players.fullInfo?player_tsid=%s', $instance['glitch_id']);
			$glitchdata = @file_get_contents($glitch_url);
			if ($glitchdata) {
				file_put_contents($cache_url, $glitchdata);
				$data = json_decode($glitchdata, true);
			}
		} else {
			$renew_cache = (time() - filemtime($cache_url) > (60 * 15)) ? true : false;
		}
		
		if (!$data) {
			// No data yet
			$data = json_decode(file_get_contents($cache_url), true);
		}
		
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		echo $before_title . $title . $after_title;
		$last_seen = ($data['last_online'] == 0) ? 'online now!' : $this->time_ago($data['last_online']);
		echo '
			<div data-renew="'.$renew_cache.'" data-tsid="'.$instance['glitch_id'].'" class="glitchprofile" id="glitchprofile-widget-'.$instance['glitch_id'].'">
				<h2><a target="_blank" href="http://www.glitch.com/profiles/'.$data['player_tsid'].'/">'.$data['player_name'].'</a></h2>
				<img src="'.$data['avatar'][100].'" class="glitchprofile-avatar"  alt="'.$data['player_name'].'" title="'.$data['player_name'].'" />
				<p><strong>Last seen</strong>:<br> '.$last_seen.'</p>
				<p class="glitchprofile-list glitchprofile-list-imagination">Level '.$data['stats']['level'].'</p>
				<p class="glitchprofile-list glitchprofile-list-energy">'.$data['stats']['energy_max'].' energy</p>
				<p class="glitchprofile-list glitchprofile-list-braincapacity">'.$data['stats']['brain_capacity'].' braincapacity</p>
				<p class="glitchprofile-list glitchprofile-list-mood">'.$data['stats']['quoin_multiplier'].'x</p>
				<p><strong>Current location</strong>:<br> <a target="_blank" href="http://www.glitch.com/locations/'.$data['location']['tsid'].'/">'.$data['location']['name'].'</a></p>
				<p><strong>Latest skill</strong> (Total '.$data['num_skills'].'):<br> '.$data['latest_skill']['name'].'</p>
				<p><strong>Latest achievement</strong> (Total '.$data['num_achievements'].'):<br> <a target="_blank" href="http://www.glitch.com/profiles/'.$instance['glitch_id'].'/achievements/'.$this->glitchify($data['latest_achievement']['name']).'/">'.$data['latest_achievement']['name'].'</a>
				<br><img src="'.$data['latest_achievement']['icon_urls'][60].'" alt="'.$data['latest_achievement']['name'].'" />
				</p>
			</div>
		';
		echo $after_widget;
	}
	
	function glitchify($name) {
		$name = strtolower($name);
		$name = str_replace(' ', '-', $name);
		return $name;
	}
	
	function time_ago( $datefrom , $dateto=-1 ) { 
		if($datefrom<=0) { return "A long time ago"; } 
		if($dateto==-1) { $dateto = time(); } 

		$difference = $dateto - $datefrom; 

		// Seconds 
		if($difference < 60) 
		{ 
		  $time_ago   = $difference . ' second' . ( $difference > 1 ? 's' : '' ).' ago'; 
		} 

		// Minutes 
		else if( $difference < 60*60 ) 
		{ 
			$ago_seconds   = $difference % 60; 
			$ago_seconds   = ( ( $ago_seconds AND $ago_seconds > 1 ) ? ' and '.$ago_seconds.' seconds' : ( $ago_seconds == 1 ? ' and '.$ago_seconds.' second' : '' ) ); 
			$ago_minutes   = floor( $difference / 60 ); 
			$ago_minutes   = $ago_minutes . ' minute' . ( $ago_minutes > 1 ? 's' : '' ); 
			$time_ago      = $ago_minutes.$ago_seconds.' ago'; 
		} 

		// Hours 
		else if ( $difference < 60*60*24 ) 
		{ 
			$ago_minutes   = round( $difference / 60 ) % 60 ; 
		   $ago_minutes   = ( ( $ago_minutes AND $ago_minutes > 1 ) ? ' and ' . $ago_minutes . ' minutes' : ( $ago_minutes == 1 ? ' and ' . $ago_minutes .' minute' : '' )); 
		   $ago_hours      = floor( $difference / ( 60 * 60 ) ); 
		   $ago_hours      = $ago_hours . ' hour'. ( $ago_hours > 1 ? 's' : '' ); 
		   $time_ago      = $ago_hours.$ago_minutes.' ago'; 
		} 

		// Days 
		else if ( $difference < 60*60*24*7 ) 
		{ 
		  $ago_hours      = round( $difference / 3600 ) % 24 ; 
		  $ago_hours      = ( ( $ago_hours AND $ago_hours > 1 ) ? ' and ' . $ago_hours . ' hours' : ( $ago_hours == 1 ? ' and ' . $ago_hours . ' hour' : '' )); 
		  $ago_days      = floor( $difference / ( 3600 * 24 ) ); 
		  $ago_days      = $ago_days . ' day' . ($ago_days > 1 ? 's' : '' ); 
		  $time_ago      = $ago_days.$ago_hours.' ago'; 
		} 

		// Weeks 
		else if ( $difference < 60*60*24*30 ) 
		{ 
		  $ago_days      = round( $difference / ( 3600 * 24 ) ) % 7; 
		  $ago_days      = ( ( $ago_days AND $ago_days > 1 ) ? ' and '.$ago_days.' days' : ( $ago_days == 1 ? ' and '.$ago_days.' day' : '' )); 
		  $ago_weeks      = floor( $difference / ( 3600 * 24 * 7) ); 
		  $ago_weeks      = $ago_weeks . ' week'. ($ago_weeks > 1 ? 's' : '' ); 
		  $time_ago      = $ago_weeks.$ago_days.' ago'; 
		} 

		// Months 
		else if ( $difference < 60*60*24*365 ) 
		{ 
		  $days_diff   = round( $difference / ( 60 * 60 * 24 ) ); 
		  $ago_days   = $days_diff %  30 ; 
		  $ago_weeks   = round( $ago_days / 7 ) ; 
		  $ago_weeks   = ( ( $ago_weeks AND $ago_weeks > 1 ) ? ' and '.$ago_weeks.' weeks' : ( $ago_weeks == 1 ? ' and '.$ago_weeks.' week' : '' ) ); 
		  $ago_months   = floor( $days_diff / 30 ); 
		  $ago_months   = $ago_months .' month'. ( $ago_months > 1 ? 's' : '' ); 
		  $time_ago   = $ago_months.$ago_weeks.' ago'; 
		} 

		// Years 
		else if ( $difference >= 60*60*24*365 ) 
		{ 
		  $ago_months   = round( $difference / ( 60 * 60 * 24 * 30.5 ) ) % 12; 
		  $ago_months   = ( ( $ago_months AND $ago_months > 1 ) ? ' and ' . $ago_months . ' months' : ( $ago_months == 1 ? ' and '.$ago_months.' month' : '' ) ); 
		  $ago_years   = floor( $difference / ( 60 * 60 * 24 * 365 ) );#30 * 12 
		  $ago_years   = $ago_years . ' year'. ($ago_years > 1 ? 's' : '' ) ; 
		  $time_ago   = $ago_years.$ago_months.' ago'; 
		} 

		return $time_ago; 
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("GlitchProfileWidget");') );

?>