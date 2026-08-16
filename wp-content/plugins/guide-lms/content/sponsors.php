<?php
/**
 * Demo sponsorship campaigns, one per slot.
 *
 * Seeded as `submitted` — awaiting review — rather than live, on purpose.
 *
 * A live campaign renders on the public site as "Sponsored by X", which is a
 * statement to every visitor that X is paying to be there. Shipping that as
 * demo data would put a fabricated commercial endorsement in front of real
 * learners, and the whole argument of this platform is that it does not do
 * that sort of thing.
 *
 * Pending is the useful state anyway: the campaigns appear in LMS → Sponsors
 * with realistic copy, so the review, approval, payment and expiry flow can be
 * walked end to end before a real sponsor ever arrives. Approving one is a
 * single click when somebody wants to see a filled slot.
 *
 * Meanwhile the public site is not empty: Ads::render_house() offers each
 * unsold slot for sale, which is honest, useful, and the only advertising the
 * sponsorship product gets.
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'     => 'demo-leaderboard',
		'company'  => 'Example Corp',
		'slot'     => 'leaderboard',
		'headline' => 'Hiring 40 freshers this year',
		'body'     => 'We train on the job, we pay from day one, and we do not ask for a bond.',
		'url'      => 'https://example.com/careers',
		'months'   => 3,
	),

	array(
		'slug'     => 'demo-square',
		'company'  => 'Example Cloud',
		'slot'     => 'square',
		'headline' => 'Free tier for students',
		'body'     => 'Deploy your first project on a real server. No card required.',
		'url'      => 'https://example.com/students',
		'months'   => 1,
	),

	array(
		'slug'     => 'demo-badge',
		'company'  => 'Example Labs',
		'slot'     => 'badge',
		'headline' => 'Practice interviews, free',
		'body'     => 'Mock rounds with working engineers.',
		'url'      => 'https://example.com/mock',
		'months'   => 6,
	),
);
