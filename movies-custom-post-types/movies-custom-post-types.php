<?php
/*
 * Plugin Name:       Movies Custom Post Types
 * Plugin URI:        https://www.ammaraslam.com/
 * Description:       Movies single and archive templates with custom post types.
 * Version:           1.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ammar Aslam
 * Author URI:        https://www.ammaraslam.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       movies-custom-post-types
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}


/*
* Creating a function to create our CPT
*/

/* Including CSS & JS Files in the Plugin*/

function add_style()
{

    wp_register_style('style', plugin_dir_url(__FILE__).'css/mcpt.css');
    wp_enqueue_style('style', plugin_dir_url(__FILE__).'css/mcpt.css');
    wp_register_style('bootstrap_style', plugin_dir_url(__FILE__).'css/bootstrap.min.css');
    wp_enqueue_style('bootstrap_style', plugin_dir_url(__FILE__).'css/bootstrap.min.css');

    wp_register_script('movies-script', plugin_dir_url(__FILE__) . 'js/movies-admin.js');
    wp_enqueue_script('movies-admin-script', plugin_dir_url(__FILE__) . 'js/movies-admin.js', array('jquery'), null, true);

}
add_action('wp_enqueue_scripts', 'add_style');

// Load single template for 'movies' custom post type
function template_movies($single)
{
    global $post;

    if ($post->post_type === 'movies') {
        $single_template = plugin_dir_path(__FILE__) . 'templates/single-template.php';
        if (file_exists($single_template)) {
            return $single_template;
        }
    }

    return $single;
}
add_filter('single_template', 'template_movies');

// Load archive template for 'movies' custom post type
function archive_template_movies($archive)
{
    $post_type = 'movies';
    $archive_template = plugin_dir_path(__FILE__) . 'templates/archive-movies.php';

    if (is_post_type_archive($post_type) && file_exists($archive_template)) {
        return $archive_template;
    }

    return $archive;
}
add_filter('archive_template', 'archive_template_movies');

// ------------------ taxonomy category template
function custom_movie_category_template($template)
{
    // Check if this is the custom taxonomy 'movie_category' archive
    if (is_tax('movie_category')) {
        // Look for a template file in your plugin directory
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-movie_category.php';
        
        // If the file exists, use it
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    // Otherwise, return the default template
    return $template;
}
add_filter('template_include', 'custom_movie_category_template');




function custom_post_type_movies()
{
    global $post;
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x('Movies', 'Post Type General Name'),
        'singular_name'       => _x('Movie', 'Post Type Singular Name'),
    );
    // Set other options for Custom Post Type
    $args = array(
        'labels'              => $labels,
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'   => true,
        'rewrite'   => array("Slug" => "Book"),
        'menu_position'       => null,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor','thumbnail'),
        'menu_icon' => 'dashicons-video-alt2',
        'taxonomies'         => array( 'movie_category' ),
    );
    global $post;
    // Registering your Custom Post Type
    register_post_type('movies', $args);
}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not
* unnecessarily executed.
*/

add_action('init', 'custom_post_type_movies');

// registering the taxonomy like the categories of the CPTs
function create_movie_taxonomy()
{
    $labels = array(
        'name'              => _x('Movie Categories', 'taxonomy general name', 'textdomain'),
        'singular_name'     => _x('Movie Category', 'taxonomy singular name', 'textdomain'),
        'search_items'      => __('Search Movie Categories', 'textdomain'),
        'all_items'         => __('All Movie Categories', 'textdomain'),
        'parent_item'       => __('Parent Movie Category', 'textdomain'),
        'parent_item_colon' => __('Parent Movie Category:', 'textdomain'),
        'edit_item'         => __('Edit Movie Category', 'textdomain'),
        'update_item'       => __('Update Movie Category', 'textdomain'),
        'add_new_item'      => __('Add New Movie Category', 'textdomain'),
        'new_item_name'     => __('New Movie Category Name', 'textdomain'),
        'menu_name'         => __('Movie Category', 'textdomain'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'movie-category' ),
    );
    
    register_taxonomy('movie_category', array( 'movies' ), $args);
}
add_action('init', 'create_movie_taxonomy', 0);
    


// Adding custom fields related to the movies

add_action('admin_init', 'cf_movies');
function cf_movies()
{
    global $post;
    add_meta_box(
        'cf_movies_id',           //ID
        'Movies Custom Field',              //title of the custom field
        'cf_callback_metabox',              // metabox callback funtion
        'movies',                          //custom post type line 45
        'normal',
    );
}
function cf_callback_metabox()
{
    echo "<h2>Here is my metabox, put the value</h2><br>";
    $id = get_the_ID();
    wp_head();
    global $post;
    global $wpdb;
    $tb_name = $wpdb->prefix.'my_movies_db';
    $name = $wpdb->get_var("SELECT `myname` FROM $tb_name WHERE `id` = $id;");
    $movie = $wpdb->get_var("SELECT `movie_name` FROM $tb_name WHERE `id` = $id;");
    $country = $wpdb->get_var("SELECT `country` FROM $tb_name WHERE `id` = $id;");
    $price = $wpdb->get_var("SELECT `price` FROM $tb_name WHERE `id` = $id;");
    $currency = $wpdb->get_var("SELECT `currency` FROM $tb_name WHERE `id` = $id;");
    $video = $wpdb->get_var("SELECT `video` FROM $tb_name WHERE `id` = $id;");
    // print_r($video);
    $section = $wpdb->get_var("SELECT `section` FROM $tb_name WHERE `id` = $id;");
    // print_r($section);
    $gallery_images = $wpdb->get_var("SELECT `gallery_images` FROM $tb_name WHERE `id` = $id;");
    // print_r($gallery_images);
    ?>
<form method="POST">
    <?php wp_nonce_field('save_movie_details', 'my_movies_nonce'); ?>
    <div class="container">
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Director:</h3><input type="text" name="fname"
                value="<?php echo $name; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Movie Name:</h3> <input type="text" name="movie"
                value="<?php echo $movie; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Country:</h3> <input type="text" name="country"
                value="<?php echo $country; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Price:</h3> <input type="text" name="price"
                value="<?php echo $price; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Currency:</h3> <input type="text" name="currency"
                value="<?php echo $currency; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Youtube Link:</h3> <input type="text" name="video"
                value="<?php echo $video; ?>">
        </div>
        <br>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Highlights:</h3> <input type="text" name="section"
                value="<?php echo $section; ?>">
        </div>
        <div class="p-3 mb-2 bg-dark text-white">
            <h3>Trailor Images:</h3>
            <input type="hidden" name="gallery_images" id="gallery_images"
                value="<?php echo esc_attr($gallery_images); ?>" />
            <button type="button" id="upload_gallery_button" class="button btn">Upload Images</button>
            <div id="gallery_preview" style="margin-top:10px;">
                <?php if ($gallery_images) :
                    $gallery_images = explode(',', $gallery_images);
                    foreach ($gallery_images as $image_id) :
                        $image_url = wp_get_attachment_url($image_id);
                        ?>
                <img src="<?php echo esc_url($image_url); ?>"
                    style="max-width:100px; margin-right:10px;" />
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>




    </div>
</form>

<?php

}

/* Creating a database on plugin activation */

function database_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix."my_movies_db";
    $charset = $wpdb->get_charset_collate();
    $sql_movie = "CREATE TABLE $table_name (
        id             int(9) NOT NULL,
        myname         text(100) NOT NULL,
        movie_name     varchar(100) NOT NULL,
        country        varchar(100) NOT NULL,
        price          int(9) NOT NULL,
        currency       varchar(100) NOT NULL,
        video          varchar(100) NOT NULL,
        section        varchar(256) NOT NULL,
        gallery_images text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_movie);

}
register_activation_hook(__FILE__, 'database_table');

// Save Movies Details to the Database
add_action('save_post', 'insertion');
function insertion($post_id)
{


    // Check if this is an auto save routine.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify the nonce before proceeding.
    if (!isset($_POST['my_movies_nonce']) || !wp_verify_nonce($_POST['my_movies_nonce'], 'save_movie_details')) {
        return;
    }

    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "my_movies_db";

    // Retrieve and sanitize form data
    $name = isset($_POST['fname']) ? sanitize_text_field($_POST['fname']) : '';
    $movie = isset($_POST['movie']) ? sanitize_text_field($_POST['movie']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $price = isset($_POST['price']) ? intval($_POST['price']) : 0;
    $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : '';
    $video = isset($_POST['video']) ? sanitize_text_field($_POST['video']) : '';
    $section = isset($_POST['section']) ? sanitize_textarea_field($_POST['section']) : '';
    $gallery_images = isset($_POST['gallery_images']) ? sanitize_text_field($_POST['gallery_images']) : '';

    // Check if the record already exists
    $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE id = %d", $post_id));
    // var_dump($existing_id);
    // exit;




    // Insert or update the database
    if ($existing_id) {
        // Update existing record
        $wpdb->update(
            $table_name,
            [
                'myname' => $name,
                'movie_name' => $movie,
                'country' => $country,
                'price' => $price,
                'currency' => $currency,
                'video' => $video,
                'section' => $section,
                'gallery_images' => $gallery_images,
            ],
            ['id' => $post_id],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s'],
            ['%d']
        );
    } else {
        // Insert new record
        $wpdb->insert(
            $table_name,
            [
                'id' => $post_id,
                'myname' => $name,
                'movie_name' => $movie,
                'country' => $country,
                'price' => $price,
                'currency' => $currency,
                'video' => $video,
                'section' => $section,
                'gallery_images' => $gallery_images,
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
    }
}








?>