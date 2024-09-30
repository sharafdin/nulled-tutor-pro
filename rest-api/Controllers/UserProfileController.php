<?php
/**
 * User Profile Controller
 *
 * Manage API for user profile
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.7.0
 */

namespace TutorPro\RestAPI\Controllers;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use TUTOR_PRO\DeviceManagement;
use WP_REST_Request;
use WP_User;



if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * User Controller class
 */
class UserProfileController extends BaseController {

	/**
	 * Fillable fields for user profile
	 *
	 * @since 2.7.0
	 * @var array
	 */
	private $fillable_fields = array(
		'user_id',
		'first_name',
		'last_name',
		'phone_number',
		'job_title',
		'profile_bio',
		'facebook',
		'twitter',
		'linkedin',
		'website',
		'github',
	);


	/**
	 * Maximum file size of 2MB for file upload
	 *
	 * @since 2.7.0
	 *
	 * @var integer
	 */
	private $file_upload_max_size = 2000000;

	/**
	 * Required fields for user profile
	 *
	 * @since 2.7.0
	 *
	 * @var array
	 */

	private $required_fields = array(
		'user_id',
	);

	/**
	 * Required params for photo upload
	 *
	 * @since 2.7.0
	 *
	 * @var array
	 */
	private $photo_params = array( 'profile-photo', 'cover-photo' );

	/**
	 * Operation codes
	 *
	 * @since 2.7.0
	 *
	 * @var string
	 */
	public $operation = 'user_profile';


	/**
	 * Intialize class constructor.
	 */
	public function __construct() {
		parent::__construct();
	}




	/**
	 * Get user profile data
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request data.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_user_profile( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params()
		);

		// Validate Request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {

			return $this->validation_error_response( $validation->errors, $this->code_read );
		}

		$user_id = $params['user_id'];
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return $this->response(
				$this->code_read,
				__( 'Invalid user', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		try {
			$data = $this->get_user_profile_data( $user );

			return $this->response(
				$this->code_read,
				__( 'Profile information retrieved successfully', 'tutor-pro' ),
				$data
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Profile information retrieval failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}


	/**
	 * Update user password
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_user_password( WP_REST_Request $request ) {

		// Sanitize request params.
		$params = Input::sanitize_array(
			$request->get_params(),
		);

		// Setup required fields.
		$this->setup_required_fields(
			$params,
			array(
				'user_id',
				'current_password',
				'new_password',
				'confirm_new_password',
			)
		);

		// Get required parameter variable from params.
		$user_id              = $params['user_id'];
		$current_password     = $params['current_password'];
		$new_password         = $params['new_password'];
		$confirm_new_password = $params['confirm_new_password'];

		// Validate required fields.
		$validation = $this->validate( $params );
		if ( $new_password !== $confirm_new_password ) {
			$validation->success                        = false;
			$validation->errors['confirm_new_password'] = __( 'New password and confirm password does not match', 'tutor-pro' );
		}

		if ( ! $validation->success ) {

			return $this->validation_error_response( $validation->errors, $this->code_update );
		}

		$user = get_userdata( $user_id );
		try {

			$check_current_password = wp_check_password( $current_password, $user->user_pass, $user_id );

			if ( ! $check_current_password ) {
				return $this->response(
					$this->code_update,
					__( 'Current password provided is invalid', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}

			wp_set_password( $new_password, $user_id );
			return $this->response(
				$this->code_update,
				__( 'Password updated successfully', 'tutor-pro' )
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Failed to update user password', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Update user profile
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_user_profile( WP_REST_Request $request ) {
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'profile_bio' => 'wp_kses_post',
			)
		);

		// Extract parameters from request.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set required fields to empty if not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate params.
		$validation = $this->validate( $params );

		if ( isset( $params['phone_number'] ) && ! preg_match( '/^[\d()\-]+$/', $params['phone_number'] ) ) {
			$validation->success                = false;
			$validation->errors['phone_number'] = __( 'Invalid phone number', 'tutor-pro' );
		}

		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_update );
		}

		$user_id = $params['user_id'];

		$user = get_userdata( $user_id );

		try {
			isset( $params['first_name'] ) ? $user->first_name = $params['first_name'] : $user->first_name;
			isset( $params['last_name'] ) ? $user->last_name   = $params['last_name'] : $user->last_name;

			if ( isset( $params['job_title'] ) ) {
				update_user_meta( $user_id, '_tutor_profile_job_title', $params['job_title'] );
			}

			if ( isset( $params['profile_bio'] ) ) {
				update_user_meta( $user_id, '_tutor_profile_bio', $params['profile_bio'] );
			}

			if ( isset( $params['phone_number'] ) ) {
				update_user_meta( $user_id, 'phone_number', $params['phone_number'] );
			}

			if ( isset( $params['facebook'] ) ) {
				update_user_meta( $user_id, '_tutor_profile_facebook', $params['facebook'] );
			}

			if ( isset( $params['twitter'] ) ) {

				update_user_meta( $user_id, '_tutor_profile_twitter', $params['twitter'] );

			}
			if ( isset( $params['linkedin'] ) ) {

				update_user_meta( $user_id, '_tutor_profile_linkedin', $params['linkedin'] );

			}
			if ( isset( $params['website'] ) ) {
				update_user_meta( $user_id, '_tutor_profile_website', $params['website'] );
			}

			if ( isset( $params['github'] ) ) {
				update_user_meta( $user_id, '_tutor_profile_github', $params['github'] );
			}

			wp_update_user( $user );

			$data = $this->get_user_profile_data( $user );

			return $this->response(
				$this->code_update,
				__( 'User profile updated successfully', 'tutor-pro' ),
				$data,
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Failed to update user profile', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Add new profile photo or cover photo
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function set_profile_photo( WP_REST_Request $request ) {

		$params = Input::sanitize_array(
			$request->get_params(),
		);
		// Check whether the photo type parameter is valid.
		if ( ! key_exists( $params['photo_type'], array_flip( $this->photo_params ) ) ) {
			return $this->response(
				$this->code_create,
				__( 'Parameter \'photo_type\' is not valid', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}
		// Get the uploaded photo file field.
		$photo_file = tutor_utils()->array_get( 'photo_file', $_FILES ); //phpcs:ignore
		$size       = is_array( $photo_file ) ? $photo_file['size'] : null;
		// Set the mime type to validate file type.
		$params['file'] = is_array( $photo_file ) ? $photo_file['type'] : '';

		// Setup required fields.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate params.
		$validation = $this->validate( $params );

		// Maximum file upload size 2MB.
		if ( $size && $size > $this->file_upload_max_size ) {
			$validation->success        = false;
			$validation->errors['file'] = __( 'Maximum upload file size exceeded', 'tutor-pro' );
		}

		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_create );
		}

		$user_id    = $params['user_id'];
		$photo_type = $params['photo_type'];

		$meta_key              = 'cover-photo' === $photo_type ? '_tutor_cover_photo' : '_tutor_profile_photo';
		$profile_photo_message = __( 'Profile photo added successfully', 'tutor-pro' );
		$cover_photo_message   = __( 'Cover photo added successfully', 'tutor-pro' );

		try {

			// Override default variables.
			$upload_overrides = array( 'test_form' => false );

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			// Upload the file to wp-contents/uploads.
			$movefile = wp_handle_upload( $photo_file, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = tutor_utils()->array_get( 'file', $movefile );
				$file_url  = tutor_utils()->array_get( 'url', $movefile );

				if ( file_exists( $file_path ) ) {

					$image_info = getimagesize( $file_path );
					$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';

				}
				$media_id = wp_insert_attachment(
					array(
						'guid'           => $file_path,
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					),
					$file_path,
					0
				);
				if ( $media_id ) {

					// To use the wp_generate_metadata() function.
					require_once ABSPATH . 'wp-admin/includes/image.php';

					// Save attachment metas into database.
					wp_update_attachment_metadata( $media_id, wp_generate_attachment_metadata( $media_id, $file_path ) );

					// Save user metadata for photo.
					$this->delete_photo( $user_id, $meta_key );
					update_user_meta( $user_id, $meta_key, $media_id );

					return $this->response(
						$this->code_create,
						'cover-photo' === $photo_type ? $cover_photo_message : $profile_photo_message
					);
				}
			} else {
				return $this->response(
					$this->code_create,
					__( 'Error uploading photo', 'tutor-pro' ),
					$movefile['error'],
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Error uploading photo', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Remove user profile photos
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function remove_profile_photo( WP_REST_Request $request ) {

		$params = Input::sanitize_array(
			$request->get_params(),
		);
		// Check whether the photo type parameter is valid.
		if ( ! key_exists( $params['photo_type'], array_flip( $this->photo_params ) ) ) {
			return $this->response(
				$this->code_create,
				__( 'Parameter \'photo_type\' is not valid', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		// Set required fields to empty if not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate params.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_delete );
		}
		try {
			$user_id               = $params['user_id'];
			$meta_key              = 'cover-photo' === $params['photo_type'] ? '_tutor_cover_photo' : '_tutor_profile_photo';
			$profile_photo_message = __( 'Profile photo deleted successfully', 'tutor-pro' );
			$cover_photo_message   = __( 'Cover photo deleted successfully', 'tutor-pro' );
			$message               = 'cover-photo' === $params['photo_type'] ? $cover_photo_message : $profile_photo_message;

			$this->delete_photo( $user_id, $meta_key );
			return $this->response(
				$this->code_delete,
				$message
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Error deleting photo', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Get social links
	 *
	 * @since 2.7.0
	 *
	 * @param int $user_id user id.
	 *
	 * @return array
	 */
	private function get_socials( int $user_id ) {

		$data = array();

		$socials = tutor_utils()->tutor_user_social_icons();

		foreach ( $socials as $key => $value ) {
			$data_key          = str_replace( '_tutor_profile_', '', $key );
			$data[ $data_key ] = get_user_meta( $user_id, $key, true );
		}

		return $data;
	}

	/**
	 * Delete upload photo
	 *
	 * @since 2.7.0
	 *
	 * @param int    $user_id user id.
	 *
	 * @param string $meta_key meta key for user meta table.
	 *
	 * @return void
	 */
	private function delete_photo( int $user_id, string $meta_key ) {
		$photo_id = get_user_meta( $user_id, $meta_key, true );

		if ( is_numeric( $photo_id ) ) {
			wp_delete_attachment( $photo_id, true );
		}

		delete_user_meta( $user_id, $meta_key );
	}

	/**
	 * Get user profile data
	 *
	 * @since 2.7.0
	 * @param WP_User $user user object.
	 * @return object
	 */
	private function get_user_profile_data( WP_User $user ) {

		$data                    = new \stdClass();
		$data->first_name        = $user->first_name;
		$data->last_name         = $user->last_name;
		$data->display_name      = $user->display_name;
		$data->user_email        = $user->user_email;
		$data->user_name         = $user->user_login;
		$data->job_title         = get_user_meta( $user->ID, '_tutor_profile_job_title', true );
		$data->bio               = get_user_meta( $user->ID, '_tutor_profile_bio', true );
		$data->phone_number      = get_user_meta( $user->ID, 'phone_number', true );
		$data->profile_photo_url = '';
		$data->cover_photo_url   = '';

		// Prepare profile picture.
		$profile_pic_id = get_user_meta( $user->ID, '_tutor_profile_photo', true );

		if ( $profile_pic_id ) {
			$url = wp_get_attachment_image_url( $profile_pic_id, true );

			$data->profile_photo_url = ! empty( $url ) ? $url : '';
		}

		// Prepare cover photo.
		$cover_photo_id = get_user_meta( $user->ID, '_tutor_cover_photo', true );

		if ( $cover_photo_id ) {
			$url = wp_get_attachment_image_url( $cover_photo_id, true );

			$data->cover_photo_url = ! empty( $url ) ? $url : '';
		}
		$data->social_links = $this->get_socials( $user->ID );
		return $data;
	}





	/**
	 * Validate data
	 *
	 * @since 2.7.0
	 *
	 * @param array $data form data.
	 *
	 * @return object
	 */
	protected function validate( array $data ): object {

		$validation_rules = array(
			'user_id'              => 'required|numeric|user_exists',
			'file'                 => 'required|mimes:image/jpeg,image/png,image/jpg',
			'current_password'     => 'required',
			'new_password'         => 'required',
			'confirm_new_password' => 'required',
		);

		// Skip validation rules for not available fields in data.
		foreach ( $validation_rules as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) ) {
				unset( $validation_rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $validation_rules, $data );
	}
}
