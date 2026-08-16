<?php
/**
 * Dynamic JSON-LD structured data.
 *
 * - Course pages: schema.org/Course with courseCode, provider, offers,
 *   hasCourseInstance (online, self-paced), and every lesson as hasPart
 *   LearningResource entries in learner order.
 * - Lesson pages: LearningResource (isPartOf the Course, timeRequired)
 *   plus a VideoObject when the lesson has a video.
 * - Learning paths: ItemList of the path's courses.
 * - Front page: WebSite + Organization.
 */

namespace Guide\Schema;

defined( 'ABSPATH' ) || exit;

class Json_Ld {

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output' ), 20 );
	}

	public static function output() {
		$graph = array();

		if ( is_front_page() ) {
			$graph = array( self::website(), self::organization() );
		} elseif ( is_singular( 'course' ) ) {
			$graph = array( self::course( get_queried_object_id() ) );
		} elseif ( is_singular( 'lesson' ) ) {
			$graph = self::lesson( get_queried_object_id() );
		} elseif ( is_singular( 'learning_path' ) ) {
			$graph = array( self::learning_path( get_queried_object_id() ) );
		} elseif ( is_singular( 'company' ) ) {
			$graph = self::company_guide( get_queried_object_id() );
		} elseif ( is_singular( 'help_article' ) ) {
			$graph = array( self::help_article( get_queried_object_id() ) );
		}

		$graph = array_values( array_filter( $graph ) );
		if ( empty( $graph ) ) {
			return;
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}

	/* --- Builders --- */

	/**
	 * A company guide is an Article *about* an Organization — not a JobPosting.
	 *
	 * JobPosting would be wrong and actively harmful: it describes a specific
	 * open vacancy with a real application URL, and search engines surface it
	 * as one. This page is editorial advice about how a company hires, which is
	 * a different thing, and claiming otherwise would put a fake vacancy into
	 * job search results.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	public static function company_guide( int $post_id ): array {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return array();
		}

		$name    = get_the_title( $post );
		$website = (string) get_post_meta( $post_id, 'jsl_company_website', true );

		$organization = array(
			'@type' => 'Organization',
			'name'  => $name,
		);

		if ( $website ) {
			$organization['url'] = $website;
		}

		$logo = get_the_post_thumbnail_url( $post_id, 'full' );

		if ( $logo ) {
			$organization['logo'] = $logo;
		}

		$article = array(
			'@type'            => 'Article',
			'headline'         => sprintf(
				/* translators: %s: company name. */
				__( 'How to get a job at %s', 'guide-lms' ),
				$name
			),
			'mainEntityOfPage' => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'about'            => $organization,
			'publisher'        => self::organization(),
		);

		if ( has_excerpt( $post ) ) {
			$article['description'] = get_the_excerpt( $post );
		}

		// The selection process reads naturally as a HowTo, and that is what it
		// is: ordered steps toward one outcome.
		$steps = \Guide\Companies\Companies::process( $post_id );

		if ( $steps ) {
			$how_to_steps = array();

			foreach ( $steps as $i => $step ) {
				$how_to_steps[] = array_filter(
					array(
						'@type'    => 'HowToStep',
						'position' => $i + 1,
						'name'     => $step['title'] ?? '',
						'text'     => $step['detail'] ?? '',
					)
				);
			}

			$article['hasPart'] = array(
				'@type' => 'HowTo',
				'name'  => sprintf(
					/* translators: %s: company name. */
					__( '%s selection process', 'guide-lms' ),
					$name
				),
				'step'  => $how_to_steps,
			);
		}

		return array( $article );
	}

	public static function help_article( int $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return null;
		}

		return array_filter(
			array(
				'@type'            => 'Article',
				'headline'         => get_the_title( $post ),
				'description'      => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
				'mainEntityOfPage' => get_permalink( $post ),
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'publisher'        => self::organization(),
			)
		);
	}

	private static function organization(): array {
		return array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);
	}

	private static function website(): array {
		return array(
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'url'         => home_url( '/' ),
			'description' => get_bloginfo( 'description' ),
			'publisher'   => array( '@id' => home_url( '/#organization' ) ),
		);
	}

	private static function course( int $course_id ): array {
		$is_paid = class_exists( 'Guide\\Payments\\Course_Access' ) && \Guide\Payments\Course_Access::is_premium( $course_id );
		$code    = class_exists( 'Guide\\Course_Meta' ) ? \Guide\Course_Meta::get_code( $course_id ) : '';
		$stats   = \Guide\Course_Api::get_stats( $course_id );

		$lessons  = array();
		$position = 0;
		foreach ( \Guide\Course_Api::get_lessons_flat( $course_id ) as $lesson ) {
			$position++;
			$minutes   = (int) get_post_meta( $lesson->ID, 'jsl_duration_minutes', true );
			$lessons[] = array_filter(
				array(
					'@type'        => 'LearningResource',
					'position'     => $position,
					'name'         => wp_specialchars_decode( get_the_title( $lesson ), ENT_QUOTES ),
					'url'          => get_permalink( $lesson ),
					'timeRequired' => $minutes ? 'PT' . $minutes . 'M' : null,
				)
			);
		}

		$categories = wp_get_post_terms( $course_id, 'course_category', array( 'fields' => 'names' ) );

		$data = array(
			'@type'              => 'Course',
			'@id'                => get_permalink( $course_id ) . '#course',
			'name'               => wp_specialchars_decode( get_the_title( $course_id ), ENT_QUOTES ),
			'description'        => self::description( $course_id ),
			'url'                => get_permalink( $course_id ),
			'provider'           => self::organization(),
			'courseCode'         => $code ?: null,
			'about'              => ! is_wp_error( $categories ) && $categories ? $categories : null,
			'numberOfLessons'    => $stats['lessons'] ?: null,
			'timeRequired'       => $stats['minutes'] ? 'PT' . $stats['minutes'] . 'M' : null,
			'image'              => get_the_post_thumbnail_url( $course_id, 'large' ) ?: null,
			'offers'             => array(
				'@type'         => 'Offer',
				'category'      => $is_paid ? 'Paid' : 'Free',
				'price'         => $is_paid ? null : '0',
				'priceCurrency' => $is_paid ? null : 'USD',
				'availability'  => 'https://schema.org/InStock',
			),
			'hasCourseInstance'  => array(
				'@type'       => 'CourseInstance',
				'courseMode'  => 'Online',
				'courseWorkload' => $stats['minutes'] ? 'PT' . $stats['minutes'] . 'M' : null,
			),
			'hasPart'            => $lessons ?: null,
		);

		$data['offers']            = array_filter( $data['offers'] );
		$data['hasCourseInstance'] = array_filter( $data['hasCourseInstance'] );

		return array_filter( $data );
	}

	private static function lesson( int $lesson_id ): array {
		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
		$minutes   = (int) get_post_meta( $lesson_id, 'jsl_duration_minutes', true );

		$resource = array_filter(
			array(
				'@type'         => 'LearningResource',
				'@id'           => get_permalink( $lesson_id ) . '#lesson',
				'name'          => wp_specialchars_decode( get_the_title( $lesson_id ), ENT_QUOTES ),
				'url'           => get_permalink( $lesson_id ),
				'description'   => self::description( $lesson_id ),
				'timeRequired'  => $minutes ? 'PT' . $minutes . 'M' : null,
				'datePublished' => get_the_date( 'c', $lesson_id ),
				'provider'      => self::organization(),
				'isPartOf'      => $course_id ? array(
					'@type' => 'Course',
					'@id'   => get_permalink( $course_id ) . '#course',
					'name'  => wp_specialchars_decode( get_the_title( $course_id ), ENT_QUOTES ),
					'url'   => get_permalink( $course_id ),
				) : null,
			)
		);

		$graph = array( $resource );

		$video_url = (string) get_post_meta( $lesson_id, 'jsl_video_url', true );
		$embed     = $video_url && class_exists( 'Guide\\Lesson_Meta' ) ? \Guide\Lesson_Meta::embed_info( $video_url ) : null;

		if ( $embed ) {
			$graph[] = array_filter(
				array(
					'@type'        => 'VideoObject',
					'name'         => wp_specialchars_decode( get_the_title( $lesson_id ), ENT_QUOTES ),
					'description'  => self::description( $lesson_id ) ?: wp_specialchars_decode( get_the_title( $lesson_id ), ENT_QUOTES ),
					'embedUrl'     => $embed['src'],
					'contentUrl'   => 'video' === $embed['type'] ? $embed['src'] : null,
					'uploadDate'   => get_the_date( 'c', $lesson_id ),
					'duration'     => $minutes ? 'PT' . $minutes . 'M' : null,
					'thumbnailUrl' => get_the_post_thumbnail_url( $lesson_id, 'large' ) ?: self::youtube_thumb( $video_url ),
				)
			);
		}

		return $graph;
	}

	private static function learning_path( int $path_id ): array {
		$items    = array();
		$position = 0;
		foreach ( \Guide\Course_Api::get_path_courses( $path_id ) as $course ) {
			$position++;
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => wp_specialchars_decode( get_the_title( $course ), ENT_QUOTES ),
				'url'      => get_permalink( $course ),
			);
		}

		return array_filter(
			array(
				'@type'           => 'ItemList',
				'name'            => wp_specialchars_decode( get_the_title( $path_id ), ENT_QUOTES ),
				'description'     => self::description( $path_id ),
				'url'             => get_permalink( $path_id ),
				'numberOfItems'   => count( $items ),
				'itemListElement' => $items ?: null,
			)
		);
	}

	/* --- Helpers --- */

	private static function description( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		$text = $post->post_excerpt ?: wp_strip_all_tags( $post->post_content );
		return wp_html_excerpt( wp_specialchars_decode( $text, ENT_QUOTES ), 300, '…' );
	}

	private static function youtube_thumb( string $url ): ?string {
		if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})~', $url, $m ) ) {
			return 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
		}
		return null;
	}
}
