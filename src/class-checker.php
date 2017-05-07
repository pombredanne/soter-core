<?php
/**
 * Checker class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the checker class.
 */
class Checker {
	/**
	 * WPScan API Client.
	 *
	 * @var Api_Client
	 */
	protected $client;

	/**
	 * Map of package types => API client methods.
	 *
	 * @var string[]
	 */
	protected $method_map = [
		'plugin' => 'plugins',
		'theme' => 'themes',
		'wordpress' => 'wordpresses',
	];

	/**
	 * Class constructor.
	 *
	 * @param Api_Client $client Api client instance.
	 */
	public function __construct( Api_Client $client ) {
		$this->client = $client;
	}

	/**
	 * Check a single package.
	 *
	 * @param  Package $package    Package instance.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_package( Package $package ) {
		$client_method = $this->get_client_method( $package );

		$response = $this->client->{$client_method}( $package->get_slug() );

		return $response->get_vulnerabilities_by_version( $package->get_version() );
	}

	/**
	 * Check a list of packages.
	 *
	 * @param  Package[] $packages List of package instances.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		return array_unique( $vulnerabilities );
	}

	/**
	 * Get the API client instance.
	 *
	 * @return Api_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Get the relevant API client method name for checking a given package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return string
	 *
	 * @throws  \InvalidArgumentException When the package type is not supported.
	 */
	protected function get_client_method( Package $package ) {
		if ( isset( $this->method_map[ $package->get_type() ] ) ) {
			return $this->method_map[ $package->get_type() ];
		}

		throw new \InvalidArgumentException(
			"Unsupported package type [{$package->get_type()}]"
		);
	}
}
