<?php
/**
 * Template Name: Single Movie
 */

get_header();

global $wpdb;
$id = get_the_ID();
$table_name = $wpdb->prefix . "my_movies_db";

$title = get_the_title();
$name = $wpdb->get_var("SELECT `myname` FROM $table_name WHERE `id` = $id");
$movie = $wpdb->get_var("SELECT `movie_name` FROM $table_name WHERE `id` = $id");
$country = $wpdb->get_var("SELECT `country` FROM $table_name WHERE `id` = $id");
$price = $wpdb->get_var("SELECT `price` FROM $table_name WHERE `id` = $id");
$currency = $wpdb->get_var("SELECT `currency` FROM $table_name WHERE `id` = $id");
$video = $wpdb->get_var("SELECT `video` FROM $table_name WHERE `id` = $id");
$sections = $wpdb->get_var("SELECT `section` FROM $table_name WHERE `id` = $id");
$gallery_images = $wpdb->get_var("SELECT `gallery_images` FROM $table_name WHERE `id` = $id");

$youtube_id = '';
if (!empty($video)) {
    parse_str(parse_url($video, PHP_URL_QUERY), $youtube_query);
    if (!empty($youtube_query['v'])) {
        $youtube_id = $youtube_query['v'];
    } else {
        $youtube_id = basename(parse_url($video, PHP_URL_PATH));
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container movie-single-wrapper">
    <div class="movie-hero text-center">
        <h1 class="movie-title"><?php echo esc_html($title); ?></h1>
        <p class="movie-subtitle">Movie details, trailer, pricing, highlights, and gallery</p>
    </div>

    <div class="row g-4 align-items-start mb-4">
        <div class="col-lg-8">
            <div class="movie-video-wrap">
                <?php if (!empty($youtube_id)) : ?>
                <iframe width="100%" height="500"
                    src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>"
                    title="<?php echo esc_attr($title); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
                <?php else : ?>
                <div class="movie-content-card">
                    <p class="mb-0">No trailer available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="movie-info-card">
                <h4>Movie Information</h4>

                <ul class="movie-meta-list">
                    <li><span class="movie-label">🎬 Director:</span>
                        <?php echo esc_html($name); ?>
                    </li>
                    <li><span class="movie-label">🎥 Title:</span>
                        <?php echo esc_html($movie); ?>
                    </li>
                    <li><span class="movie-label">🌍 Country:</span>
                        <?php echo esc_html($country); ?>
                    </li>
                    <li>
                        <span class="movie-label">⭐Rating:</span>
                        <span class="movie-rating">★★★★★</span>
                    </li>
                </ul>

                <div class="movie-price-box">
                    <div class="text-muted mb-1">Price</div>
                    <div class="price-value">
                        <?php echo esc_html($price); ?>
                        <?php echo esc_html($currency); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="movie-content-card">
                <h4>Description</h4>
                <div class="entry-summary">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="movie-sections-card">
                <h4>Hightlights</h4>
                <?php
                $content_arr = !empty($sections) ? explode('|', $sections) : array();
if (!empty($content_arr)) :
    ?>
                <ul class="movie-sections-list">
                    <?php foreach ($content_arr as $index => $section_item) : ?>
                    <li>
                        <strong>
                            <?php echo esc_html($index + 1); ?>:</strong>
                        <?php echo esc_html(trim($section_item)); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p class="mb-0">No Highlights available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="movie-gallery-card">
                <h4>Screenshots</h4>
                <div class="row">
                    <?php
        if ($gallery_images) {
            $gallery_images = explode(',', $gallery_images);

            foreach ($gallery_images as $image_id) {
                $image_url = wp_get_attachment_url($image_id);

                if ($image_url) {
                    ?>
                    <div class="col-md-4 mb-4">
                        <img class="movie-gallery-img"
                            src="<?php echo esc_url($image_url); ?>"
                            alt="Movie Gallery Image">
                    </div>
                    <?php
                }
            }
        } else {
            echo '<p class="mb-0">No Screenshots available.</p>';
        }
?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>