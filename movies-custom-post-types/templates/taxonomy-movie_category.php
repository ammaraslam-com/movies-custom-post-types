<?php
/**
 * Template Name: Category Movies
 *
 * This is the template that displays the taxonomy categories for the custom post type "movies".
 */
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<?php
get_header(); 
global $wpdb;
$table_name = $wpdb->prefix."my_movies_db";

// Get current taxonomy term
$term = get_queried_object(); // This retrieves the current taxonomy term object
$category_slug = $term->slug; // Get the slug of the current term

// Get minimum and maximum prices
$min_price = $wpdb->get_var("SELECT MIN(price) FROM $table_name");
$max_price = $wpdb->get_var("SELECT MAX(price) FROM $table_name");

$filter_min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : $min_price;
$filter_max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : $max_price;
?>

<div class="container">
    <header class="page-header">
        <h1 class="page-title">
            <?php single_term_title(); ?>
        </h1>
    </header>

    <!-- Price Range Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="min_price">Min Price:</label>
                <input type="number" name="min_price" id="min_price" class="form-control" value="<?php echo esc_attr($filter_min_price); ?>" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>">
            </div>
            <div class="col-md-4">
                <label for="max_price">Max Price:</label>
                <input type="number" name="max_price" id="max_price" class="form-control" value="<?php echo esc_attr($filter_max_price); ?>" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <?php
    // Modify the query to get all movies within the selected category and price range
    $movies = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name 
            INNER JOIN $wpdb->term_relationships tr ON tr.object_id = $table_name.id 
            INNER JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
            INNER JOIN $wpdb->terms t ON t.term_id = tt.term_id 
            WHERE t.slug = %s AND price BETWEEN %d AND %d 
            ORDER BY price ASC",
            $category_slug, $filter_min_price, $filter_max_price
        )
    );
    ?>

    <?php if ($movies) : ?>
        <div class="row movies-archive">
            <?php foreach ($movies as $movie) :
                $post_id = $movie->id;
                $post = get_post($post_id);
                setup_postdata($post);
            ?>
                <div class="col-md-4 mb-4">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_post_thumbnail(); ?>
                                </a>
                            <?php endif; ?>

                            <h2 class="entry-title">
                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                            </h2>
                        </header>

                        <div class="entry-summary">
                            <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_excerpt(); ?></a>
                            <p>Price: <?php echo esc_html($movie->price); ?> AED</p>
                        </div>
                    </article>
                </div>
            <?php endforeach; wp_reset_postdata(); ?>
        </div>

        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p><?php esc_html_e( 'Sorry, no movies matched your criteria.' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
?>
