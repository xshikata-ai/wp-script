<?php

use WPS\Ai\Application\Services\AiUtils;
use WPS\Ai\Domain\Entities\AiJob;
use WPS\Ai\Domain\ValueObjects\AiJobId;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Infrastructure\AiJobRepositoryInWpPostType;

$request_wps_credits_left = ! isset( $_POST['wps_credits_left'] ) ? 0 : (int) $_POST['wps_credits_left'];
// Update the credits left to get the latest value after processing any AI job.
if ( $request_wps_credits_left >= 0 ) {
	AiUtils::setCreditsLeft( $request_wps_credits_left );
}

$request_ai_job_id = ! isset( $_POST['ai_job_id'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['ai_job_id'] ) );
if ( '' === $request_ai_job_id ) {
	wp_send_json_error(
		array(
			'code'    => 'error',
			'message' => 'Missing ai_job_id parameter',
			'data'    => array(
				'status' => 400,
			),
		),
		400
	);
}

$request_ai_job_content = ! isset( $_POST['ai_job_content'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['ai_job_content'] ) );
if ( '' === $request_ai_job_content ) {
	wp_send_json_error(
		array(
			'code'    => 'error',
			'message' => 'Missing ai_job_content parameter',
			'data'    => array(
				'status' => 400,
			),
		),
		400
	);
}

$request_ai_job_status = ! isset( $_POST['ai_job_status'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['ai_job_status'] ) );
try {
	$ai_job_status = AiJobStatus::from( $request_ai_job_status );
} catch ( Exception $e ) {
	wp_send_json_error(
		array(
			'code'    => 'error',
			'message' => 'Invalid ai_job_status parameter: ' . $e->getMessage(),
			'data'    => array(
				'status' => 400,
			),
		),
		400
	);
	return;
}

$ai_jobs_repo = new AiJobRepositoryInWpPostType();
$ai_jobs      = $ai_jobs_repo->find(
	array(
		'ai_job_id' => AiJobId::from( $request_ai_job_id ),
	)
);

if ( 0 === count( $ai_jobs ) || ! $ai_jobs[0] instanceof AiJob ) {
	wp_send_json_error(
		array(
			'code'    => 'error',
			'message' => 'AI job not found with id ' . $request_ai_job_id,
			'data'    => array(
				'status' => 404,
			),
		),
		404
	);
}

/** @var AiJob $ai_job */
$ai_job = $ai_jobs[0];

$updated_ai_job = new AiJob(
	$ai_job->getId(),
	$ai_job->getAiJobId(),
	$ai_job_status,
	$ai_job->getType(),
	$request_ai_job_content,
	$ai_job->getParams()
);

$ai_jobs_repo->save( $updated_ai_job );

if ( ! $ai_job_status->isSuccess() ) {
	wp_send_json_success(
		array(
			'code'    => 'success',
			'message' => 'AI job updated successfully',
			'data'    => array(
				'status' => 200,
			),
		),
		200
	);
}

// Update the post title or content.
$post_id_to_update = (int) $ai_job->getParam( 'post_id_to_update', '0' );
if ( $post_id_to_update > 0 ) {
	$post_to_update = get_post( $post_id_to_update );
	if ( ! $post_to_update instanceof WP_Post ) {
		wp_send_json_error(
			array(
				'code'    => 'error',
				'message' => 'Post #' . (string) $post_id_to_update . ' should be updated but it was not found',
				'data'    => array(
					'status' => 404,
				),
			),
			404
		);
	}

	switch ( $ai_job->getType()->getValue() ) {
		case 'title':
			// Save the original title if it doesn't exist.
			$saved_original_title = get_post_meta( $post_to_update->ID, 'original_title', true );
			if ( '' === $saved_original_title ) {
				update_post_meta( $post_to_update->ID, 'original_title', $post_to_update->post_title );
			}
			// Update the post.
			wp_update_post(
				array(
					'ID'          => $post_to_update->ID,
					'post_title'  => $request_ai_job_content, // Update the title.
					'post_name'   => sanitize_title( $request_ai_job_content ), // Update the slug.
					'post_status' => 'publish', // Set the post status to publish.
				)
			);
			break;
		case 'description':
			wp_update_post(
				array(
					'ID'           => $post_to_update->ID,
					'post_content' => $request_ai_job_content, // Update the content.
				)
			);
			break;
		default:
			wp_send_json_error(
				array(
					'code'    => 'error',
					'message' => 'Unknown AI job type ' . $ai_job->getType()->getValue(),
					'data'    => array(
						'status' => 400,
					),
				),
				400
			);
	}
}

wp_send_json_success(
	array(
		'code'    => 'success',
		'message' => 'AI job updated successfully',
		'data'    => array(
			'status' => 200,
		),
	),
	200
);
