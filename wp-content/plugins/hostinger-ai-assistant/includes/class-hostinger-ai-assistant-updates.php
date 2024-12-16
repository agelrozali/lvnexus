<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Hostinger_Ai_Assistant_Updates {
	private Hostinger_Ai_Assistant_Config $config_handler;
	private const DEFAULT_PLUGIN_UPDATE_URI = 'https://hostinger-wp-updates.com?action=get_metadata&slug=hostinger-ai-assistant';
	private const CANARY_PLUGIN_UPDATE_URI = 'https://hostinger-canary-wp-updates.com?action=get_metadata&slug=hostinger-ai-assistant';

	public function __construct() {
		$this->config_handler = new Hostinger_Ai_Assistant_Config();
		$this->updates();
	}

	private function should_use_canary_uri(): bool {
		return isset( $_SERVER['H_PLATFORM'] ) && $_SERVER['H_PLATFORM'] === 'Hostinger' && isset( $_SERVER['H_CANARY'] ) && $_SERVER['H_CANARY'] === true;
	}

	private function get_plugin_update_uri( string $default = self::DEFAULT_PLUGIN_UPDATE_URI ): string {
		if ( $this->should_use_canary_uri() ) {
			return self::CANARY_PLUGIN_UPDATE_URI;
		}

		return $this->config_handler->get_config_value( 'ai_plugin_update_uri', $default );
	}

	public function updates(): void {
		$plugin_updater_uri = $this->get_plugin_update_uri();

		if ( class_exists( PucFactory::class ) ) {
			$hts_update_checker = PucFactory::buildUpdateChecker(
				$plugin_updater_uri,
				HOSTINGER_AI_ASSISTANT_ABSPATH . 'hostinger-ai-assistant.php',
				'hostinger-ai-assistant'
			);
		}
	}

}

new Hostinger_Ai_Assistant_Updates();
