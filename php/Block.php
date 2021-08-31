<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

    /**
     * The Plugin instance.
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * Instantiates the class.
     *
     * @param Plugin $plugin The plugin object.
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Adds the action to register the block.
     *
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, 'register_block' ] );
    }

    /**
     * Registers the block.
     */
    public function register_block() {
        register_block_type_from_metadata(
            $this->plugin->dir(),
            [
                'render_callback' => [ $this, 'render_callback' ],
            ]
        );
    }

    /**
     * Renders the block.
     *
     * @param array    $attributes The attributes for the block.
     * @param string   $content    The block content, if any.
     * @param WP_Block $block      The instance of this block.
     * @return string The markup of the block.
     */
    public function render_callback( $attributes, $content, $block ) {
        $post_types = get_post_types( [ 'public' => true ] );
        $class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';
        ob_start();

        ?>
        <div class="<?php echo esc_attr( $class_name ); ?>">
            <h2><?php esc_html_e( 'Post Counts', 'site-counts' ); ?></h2>

            <?php if ( $post_types ) : ?>
                <?php foreach ( $post_types as $post_type_slug ) : ?>
                    <?php
                    $post_object = get_post_type_object( $post_type_slug );
                    $post_label  = isset( $post_object->labels->name ) ? $post_object->labels->name : '';
                    $count_posts = wp_count_posts( $post_type_slug );
                    $post_count  = isset( $count_posts->publish ) ? $count_posts->publish : 0;

                    if ( 'attachment' === $post_type_slug ) {
                        $post_count = isset( $count_posts->inherit ) ? $count_posts->inherit : 0;
                    }
                    ?>

                    <?php // translators: %d: Post count, %s: Post label ?>
                    <p><?php echo sprintf( __( 'There are %1$d %2$s', 'site-counts' ), $post_count, $post_label ); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ( isset( $_GET['post_id'] ) && ! empty( $_GET['post_id'] ) ) : ?>
                <?php // translators: %d: Post id ?>
                <p><?php echo sprintf( __( 'The current post ID is %d', 'site-counts' ), absint( wp_unslash( $_GET['post_id'] ) ) ); ?></p>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }
}
