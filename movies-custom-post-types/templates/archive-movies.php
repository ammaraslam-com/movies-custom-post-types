<?php
/**
 * Template Name: Archive Movies
 *
 * This is the template that displays the archive for the custom post type "movies".
 */
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<?php
get_header();

global $wpdb;
$table_name = $wpdb->prefix . "my_movies_db";

// Get minimum and maximum prices
$min_price = $wpdb->get_var("SELECT MIN(price) FROM $table_name");
$max_price = $wpdb->get_var("SELECT MAX(price) FROM $table_name");

$filter_min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : $min_price;
$filter_max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : $max_price;
$selected_category = isset($_GET['movie_category']) ? sanitize_text_field(wp_unslash($_GET['movie_category'])) : '';

// Get movie categories
$categories = get_terms(array(
    'taxonomy'   => 'movie_category',
    'orderby'    => 'name',
    'order'      => 'ASC',
    'hide_empty' => false,
));
?>

<div class="container archive-movies-wrapper">
    <div class="archive-hero">
        <h1><?php post_type_archive_title(); ?></h1>
        <p>Browse movies by price range and category.</p>
    </div>

    <form method="GET" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="min_price" class="form-label">Min Price</label>
                <input type="number" name="min_price" id="min_price" class="form-control"
                    value="<?php echo esc_attr($filter_min_price); ?>"
                    min="<?php echo esc_attr($min_price); ?>"
                    max="<?php echo esc_attr($max_price); ?>">
            </div>

            <div class="col-md-3">
                <label for="max_price" class="form-label">Max Price</label>
                <input type="number" name="max_price" id="max_price" class="form-control"
                    value="<?php echo esc_attr($filter_max_price); ?>"
                    min="<?php echo esc_attr($min_price); ?>"
                    max="<?php echo esc_attr($max_price); ?>">
            </div>

            <div class="col-md-3">
                <label for="movie_category" class="form-label">Category</label>
                <select name="movie_category" id="movie_category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category) : ?>
                    <option
                        value="<?php echo esc_attr($category->slug); ?>"
                        <?php selected($selected_category, $category->slug); ?>>
                        <?php echo esc_html($category->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </div>
    </form>

    <?php
    if (!empty($selected_category)) {
        $post_ids = get_posts(array(
            'post_type'      => 'movies',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'movie_category',
                    'field'    => 'slug',
                    'terms'    => $selected_category,
                ),
            ),
        ));

        if (!empty($post_ids)) {
            $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

            $query = $wpdb->prepare(
                "SELECT * FROM $table_name
                 WHERE price BETWEEN %d AND %d
                 AND id IN ($placeholders)
                 ORDER BY price ASC",
                array_merge(array($filter_min_price, $filter_max_price), $post_ids)
            );

            $movies = $wpdb->get_results($query);
        } else {
            $movies = array();
        }
    } else {
        $movies = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name
                 WHERE price BETWEEN %d AND %d
                 ORDER BY price ASC",
                $filter_min_price,
                $filter_max_price
            )
        );
    }
?>

    <?php if ($movies) : ?>
    <div class="row g-4 movies-archive">
        <?php foreach ($movies as $movie) :
            $post_id = $movie->id;
            $post = get_post($post_id);

            if (!$post) {
                continue;
            }

            setup_postdata($post);

            $movie_terms = get_the_terms($post_id, 'movie_category');
            $movie_category_name = '';

            if (!empty($movie_terms) && !is_wp_error($movie_terms)) {
                $movie_category_name = $movie_terms[0]->name;
            }
            ?>
        <div class="col-md-6 col-lg-4">
            <article id="post-<?php the_ID(); ?>" <?php post_class('movie-card'); ?>>
                <div class="movie-card-thumb">
                    <a href="<?php the_permalink(); ?>"
                        title="<?php the_title_attribute(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large'); ?>
                        <?php else : ?>
                        <img src="https://via.placeholder.com/600x400?text=No+Image"
                            alt="<?php the_title_attribute(); ?>">
                        <?php endif; ?>
                    </a>

                    <div class="movie-price-badge">
                        <?php echo esc_html($movie->price); ?>
                        <?php echo esc_html($movie->currency); ?>
                    </div>
                </div>

                <div class="movie-card-body">
                    <?php if ($movie_category_name) : ?>
                    <div class="mb-2">
                        <span class="movie-category-badge">
                            <?php echo esc_html($movie_category_name); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <h2 class="movie-card-title">
                        <a href="<?php the_permalink(); ?>"
                            rel="bookmark"><?php the_title(); ?></a>
                    </h2>

                    <div class="movie-card-excerpt">
                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                    </div>

                    <div class="movie-card-footer">
                        <a href="<?php the_permalink(); ?>"
                            class="movie-card-btn">View Details</a>
                    </div>
                </div>
            </article>
        </div>
        <?php endforeach;
wp_reset_postdata(); ?>
    </div>

    <div class="mt-5">
        <?php the_posts_pagination(); ?>
    </div>
    <?php else : ?>
    <div class="empty-state">
        <h3>No Movies Found</h3>
        <p>Try changing the price range or selecting a different category.</p>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>