<?php
/**
 * Plugin Name: Reviews Slider Plugin
 * Description: A custom plugin that creates a reviews post type and a Splide.js slider shortcode.
 * Version: 1.1
 * Author: Dushyant Verma
 * Text Domain: reviews-slider-plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type: Reviews
function rsp_register_reviews_cpt() {
    $labels = array(
        'name'               => 'Reviews',
        'singular_name'      => 'Review',
        'menu_name'          => 'Reviews',
        'name_admin_bar'     => 'Review',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Review',
        'new_item'           => 'New Review',
        'edit_item'          => 'Edit Review',
        'view_item'          => 'View Review',
        'all_items'          => 'All Reviews',
        'search_items'       => 'Search Reviews',
        'not_found'          => 'No reviews found.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'supports'           => array('title', 'editor', 'thumbnail'),
        'menu_icon'          => 'dashicons-testimonial',
    );

    register_post_type('reviews', $args);
}
add_action('init', 'rsp_register_reviews_cpt');

// Enqueue Splide.js and Font Awesome CSS
function rsp_enqueue_assets() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '6.7.2');
    wp_enqueue_style('splide-css', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css');
    wp_enqueue_script('splide-js', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_assets');

// Enqueue Plugin's CSS File
function rsp_enqueue_plugin_styles() {
    wp_enqueue_style(
        'rsp-plugin-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_plugin_styles');
// Splide Slider Shortcode with Parallax Effect
function rsp_reviews_splide_shortcode() {
    ob_start(); ?>

    <div class="parallax-container">
        <div id="splide-review-slider" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php
                    $args = array(
                        'post_type'      => 'reviews',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'orderby'        => 'date',
                        'order'          => 'DESC'
                    );
                    $reviews = new WP_Query($args);

                    if ($reviews->have_posts()) :
                        while ($reviews->have_posts()) : $reviews->the_post();
                            $featured_img = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                            ?>
                            <li class="splide__slide">
                                <div class="testi-item">
                                    <?php if ($featured_img) : ?>
                                        <div class="review-image">
                                            <img src="<?php echo esc_url($featured_img); ?>" alt="<?php the_title_attribute(); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <h3><?php the_title(); ?></h3>
                                    <p><?php echo (get_the_content()); ?></p>
                                </div>
                            </li>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        echo '<p>No reviews found.</p>';
                    endif;
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
       document.addEventListener("DOMContentLoaded", function () {
    let splide = new Splide("#splide-review-slider", {
        type: "loop",
        perPage: 3,
        perMove: 1,
        focus: "center",
        gap: "20px",
        speed: 900,  // Reduce transition speed
        autoplay: true,
        interval: 3000,
        waitForTransition: false, // Prevent delays
        lazyLoad: "nearby", // Load images only when needed
        pauseOnHover: false,
        pauseOnFocus: false,
        breakpoints: {
            1024: { perPage: 3 },
            768: { perPage: 2 },
            480: { perPage: 1 },
            0: { perPage: 1 }
        }
    });

    splide.mount();
});


        // Parallax Effect
        window.addEventListener('scroll', function() {
            let scrollPosition = window.scrollY;
            document.querySelector('.parallax-container').style.backgroundPositionY = -(scrollPosition * 0.3) + 'px';
        });
    </script>

    <?php return ob_get_clean();
}
add_shortcode('reviews_slider', 'rsp_reviews_splide_shortcode');


// Add a submenu page for "How to Use"
function rsp_add_how_to_use_page() {
    add_submenu_page(
        'edit.php?post_type=reviews',
        'How to Use', 
        'How to Use', 
        'manage_options',
        'rsp-how-to-use',
        'rsp_how_to_use_page_callback' 
    );
}
add_action('admin_menu', 'rsp_add_how_to_use_page');

// Callback function for the How to Use page
function rsp_how_to_use_page_callback() {
    echo '<div class="wrap">';
    echo '<h1>How to Use</h1>';
    include plugin_dir_path(__FILE__) . 'how-to-use.php';
    echo '</div>';
}
?>
