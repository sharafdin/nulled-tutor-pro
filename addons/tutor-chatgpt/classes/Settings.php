<?php
/**
 * Manage ChatGPT Settings.
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

namespace TutorPro\ChatGPT;

/**
 * Settings Class.
 *
 * @since 2.1.8
 */
class Settings {

	const CHATGPT_API_KEY         = 'chatgpt_api_key';
	const CHATGPT_ENABLE          = 'chatgpt_enable';
	const CHATGPT_BUBBLE_POSITION = 'chatgpt_bubble_position';

	/**
	 * Register hooks.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor/options/extend/attr', array( $this, 'add_chatgpt_settings_option' ) );
	}

	/**
	 * Add ChatGPT settings to Tutor Settings > Advance section.
	 *
	 * @since 2.1.8
	 * 
	 * @param array $attr existing settings attributes.
	 * 
	 * @return array
	 */
	public function add_chatgpt_settings_option( $attr ) {
		$bubble_position = array(
			'bottom_right' => __( 'Bottom Right', 'tutor-pro' ),
			'bottom_left'  => __( 'Bottom Left', 'tutor-pro' ),
			'top_left'     => __( 'Top Left', 'tutor-pro' ),
			'top_right'    => __( 'Top Right', 'tutor-pro' ),
		);

		$chatgpt_settings = array(
			'label'      => __( 'ChatGPT', 'tutor-pro' ),
			'slug'       => 'options',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'     => self::CHATGPT_ENABLE,
					'type'    => 'toggle_switch',
					'label'   => __( 'Enable ChatGPT', 'tutor-pro' ),
					'default' => 'on',
					'desc'    => '',
				),
				array(
					'key'         => self::CHATGPT_API_KEY,
					'type'        => 'text',
					'label'       => __( 'Insert ChatGPT API Key', 'tutor-pro' ),
					'default'     => '',
					'desc'        => __( 'Find your Secret API key in your <a href="https://platform.openai.com/account/api-keys" target="blank">ChatGPT User settings</a> and paste it here.', 'tutor-pro' ),
					'placeholder' => __( 'API key', 'tutor-pro' ),
				),
				array(
					'key'     => self::CHATGPT_BUBBLE_POSITION,
					'type'    => 'select',
					'label'   => __( 'Bubble Position', 'tutor-pro' ),
					'default' => 'bottom_right',
					'options' => $bubble_position,
					'desc'    => __( 'Set the ChatGPT bubble position', 'tutor-pro' ),
				),
			),
		);

		array_push( $attr['advanced']['blocks'], $chatgpt_settings );

		return $attr;
	}
}
