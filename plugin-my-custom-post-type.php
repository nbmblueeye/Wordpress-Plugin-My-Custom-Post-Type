<?php
/**
 * Plugin Name:       My Custom Post Type
 * Description:       This Plugin will will be used to make a simple Custom Post Type for your Theme
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            nbm-blue-eye
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-custom-post-type
 */


if(!defined('ABSPATH')){
    die("You should not to be here!");
}

if(!class_exists('My_Custom_Post_Type')){

    class My_Custom_Post_Type{

        private $taxonomy_slug;

        public function __construct()
        {
            if(!defined("MY_CUSTOM_POST_TYPE_PLUGIN_DIR_PATH")){
                define("MY_CUSTOM_POST_TYPE_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
            };
            
            if(!defined("MY_CUSTOM_POST_TYPE_PLUGIN_URL")){
                define("MY_CUSTOM_POST_TYPE_PLUGIN_URL", plugins_url()."/plugin-my-custom-post-type/");
            };

          
            add_action('init', [$this, 'my_custom_post_type_include_assets']);

            add_action('init', [$this, 'add_custom_post_type']);

            call_user_func_array([$this, 'initial_custom_func'], ['']);

            add_action( 'admin_menu', [$this, 'set_up_my_custom_post_type'] );

            register_activation_hook( __FILE__, [$this, 'activate_my_post_type_table'] );
            register_deactivation_hook( __FILE__, [$this, 'unactivate_my_post_type_table'] );

        }

        public function my_custom_post_type_include_assets(){

            $pages = ["my_custom_post_type"];

            $current_page = isset($_GET['page'])? $_GET['page'] :"";

            if(in_array($current_page, $pages)){

                wp_enqueue_style( "my_custom_post_type_main_css", MY_CUSTOM_POST_TYPE_PLUGIN_URL.'assets/css/my_custom_post_type.css?gh=abcefgh', array(), '1.0.0', 'all' );

                wp_enqueue_script( "my_custom_post_type_main_js", MY_CUSTOM_POST_TYPE_PLUGIN_URL.'assets/js/my_custom_post_type.js?hg=abc', array('jquery'), '1.0.0', true );
        
                wp_localize_script( 'my_custom_post_type_main_js', 'rest_object',
                    array( 
                        'resturl' => esc_url_raw(rest_url()),
                        'restnonce' => wp_create_nonce('wp_rest'),
                    )
                );

            }

            wp_enqueue_style( "my_custom_post_type_tax_css", MY_CUSTOM_POST_TYPE_PLUGIN_URL.'assets/css/my_custom_post_type_taxonomy.css?gh=ab', array(), '1.0.0', 'all' );

            wp_enqueue_script( "my_custom_post_type_tax_js", MY_CUSTOM_POST_TYPE_PLUGIN_URL.'assets/js/my_custom_post_type_taxonomy.js?gh=abc', array('jquery'), '1.0.0', true );

        }

        public function set_up_my_custom_post_type(){
            add_menu_page(
                'My Custom Post Type',
                'My Custom Post Type',
                'manage_options',
                'my_custom_post_type',
                [$this, 'my_custom_post_type'],
                'dashicons-welcome-learn-more',
            );

        }

        public function my_custom_post_type(){
            include_once MY_CUSTOM_POST_TYPE_PLUGIN_DIR_PATH.'views/my-custom-post-type-input-form.php';
        }

        public function reset_my_custom_post_type_table_name(){
            global $wpdb;
            return $wpdb->prefix . "my_custom_post_type"; // wp_my_custom_post_type
        }


        public function activate_my_post_type_table(){

            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

           
            if($wpdb->get_var('SHOW TABLES LIKE "'.$this->reset_my_custom_post_type_table_name().'"') == ""){

                $sql = "CREATE TABLE `".$this->reset_my_custom_post_type_table_name()."` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `post_name` text DEFAULT NULL,
                    `category_name` text DEFAULT NULL,
                    `tag_name` text DEFAULT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

                dbDelta( $sql );

            }

            $wpdb->query( 
                $wpdb->prepare( 
                    "INSERT INTO ". $this->reset_my_custom_post_type_table_name()." (`post_name`, `category_name`, `tag_name`) VALUES (%s, %s, %s)",  "News", "Categories", "Tags"
                )
            );

        }
/* <======== Deactivate Database Table =============> */      

        public function unactivate_my_post_type_table(){
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS ".$this->reset_my_custom_post_type_table_name());

        }

/* <======== Add REST ROUTE Function =============> */

        public function initial_custom_func($request){

            include_once MY_CUSTOM_POST_TYPE_PLUGIN_DIR_PATH.'library/my-custom-post-type-rest-route.php';
        
        }

/* <======== Add My Custom Post Type =============> */

        public function add_custom_post_type(){

            global $wpdb;

            $tablename = $this->reset_my_custom_post_type_table_name();
            
            $post = $wpdb->get_results(
                "SELECT * FROM {$tablename} ORDER BY `id` DESC"
            );
        
            $post_name = !empty($post[0]->post_name) ? $post[0]->post_name:"" ;
            $category_name = !empty($post[0]->category_name) ? $post[0]->category_name:"" ;
            $tag_name = !empty($post[0]->tag_name) ? $post[0]->tag_name:"" ;

            $singular = "";
            if(!empty($post_name)){
                $str_len = (int)strlen($post_name) - 1;
                if(strpos($post_name,"s",-$str_len) == $str_len){
                    $singular = rtrim($post_name,"s");
                }else{
                    $singular = $post_name;
                }
            }

            $labels = array(
                'name'                  => __( ucfirst($post_name)  , "my-custom-post-type"),
                'singular_name'         => __( ucfirst($singular) , "my-custom-post-type"),
                'menu_name'             => __( ucfirst($post_name) , "my-custom-post-type"),
                'name_admin_bar'        => __( ucfirst($post_name) , "my-custom-post-type"),
                'add_new'               => __( 'Add '.$singular, "my-custom-post-type" ),
                'add_new_item'          => __( 'Add new '.$singular, 'my-custom-post-type' ),
                'edit_item'             => __( 'Edit '.$singular, "my-custom-post-type"),
                'view_item'             => __( 'View '.$singular, "my-custom-post-type" ),
                'all_items'             => __( 'All '.$post_name, "my-custom-post-type" ),
                'search_items'          => __( 'Search '.$post_name, "my-custom-post-type" ),
                'parent_item_colon'     => __( 'Parent:'.$post_name, "my-custom-post-type" ),
                'not_found'             => __( 'No '.$post_name.' found.', "my-custom-post-type" ),
               
            );     
            $args = array(
                'labels'             => $labels,
                'description'        => 'Add '.$post_name,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => array( 'slug' => strtolower($post_name) ),
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => 60,
                'menu_icon'          => 'dashicons-welcome-learn-more',
                'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'  ),
                'taxonomies'         => array(),
                'show_in_rest'       => true
            );
             
            register_post_type(strtolower($post_name), $args );
        
            $args = array(
                'label'         => ucfirst($category_name),
                'public'        => true,
                'hierarchical'  => true,
                'query_var'     => true,
                'has_archive'   => true,
                'show_admin_column' => true,
                'show_in_menu' => true,
                "show_in_nav_menus" => true,
                'show_ui'           => true,
                "show_in_rest"  => true,
                'rewrite'       => array( 'slug' => strtolower($post_name)."_cat" ),
                
            );
        
            register_taxonomy(strtolower($post_name)."_cat", strtolower($post_name), $args);


            $args = array(
                'label'         => ucfirst($tag_name),
                'public'        => true,
                'hierarchical'  => false,
                'query_var'     => true,
                'has_archive'   => true,
                'show_admin_column' => true,
                'show_in_menu' => true,
                "show_in_nav_menus" => true,
                'show_ui'           => true,
                "show_in_rest"  => true,
                'rewrite'       => array( 'slug' => strtolower($post_name)."_tag" ),
                
            );
        
            register_taxonomy(strtolower($post_name)."_tag", strtolower($post_name), $args);

            /* Add image to Category Field*/
            $this->taxonomy_slug = strtolower($post_name).'_cat';
            add_action( $this->taxonomy_slug.'_add_form_fields', [$this, 'my_custom_post_type_add_image_to_category_field']);
            add_action( $this->taxonomy_slug.'_edit_form_fields', [$this, 'my_custom_post_type_edit_image_to_category_field'] ,10, 2);
            add_filter( 'manage_edit-'.$this->taxonomy_slug.'_columns', [$this, 'my_custom_post_type_add_new_column']);
            add_action( 'manage_'.$this->taxonomy_slug.'_custom_column', [$this, 'my_custom_post_type_add_info_to_custom_columns'], 20, 3 );

            add_action( 'created_'.$this->taxonomy_slug, [$this, 'my_custom_post_type_created_tax_image'] );
            add_action( 'edited_'.$this->taxonomy_slug, [$this, 'my_custom_post_type_edited_tax_image'] );

        }


    /* <======== Add Image to Category Fields=============> */
         public function my_custom_post_type_add_image_to_category_field(){
            ?>     
                <div class="my_custom_post_type_term-category-image-wrap">
                    <input type="hidden" id="my_custom_post_type_category_image_id" name="my_custom_post_type_category_image_id">
                    <div class="my_custom_post_type_category_image_box">
                        <div class="image-text">Add Image</div>  
                    </div>
                    <div class="my_custom_post_type_category_image_box_show mt-3" style="display:none">
                        <img src="" alt="amcpt_image">
                        <div class="my_custom_post_type_category_image_remove_image">
                            X
                        </div>
                    </div>
                    <label class="p-0 text-muted">(Add Image for <?php echo esc_html($this->taxonomy_slug);?>)</label>
                </div>     
            <?php
        }

    /* <======== Edit Category Fields=============> */
        public function my_custom_post_type_edit_image_to_category_field($term, $taxonomy){
                $value = get_term_meta($term->term_id, $this->taxonomy_slug.'_my_custom_post_type_image_id', true);
                ?>
                    <tr class="form-field">
                        <th scope="row"><label for="amcpt_image_id">Taxonomy Image</label></th>
                        <td>
                            <div class="my_custom_post_type_term-category-image-wrap my-0">
                                <input type="hidden" id="my_custom_post_type_category_image_id" name="my_custom_post_type_category_image_id" value="<?php echo esc_attr(absint($value)) ?>">
                                
                                <div class="my_custom_post_type_category_image_box mt-3" style="display:none">
                                    <div class="image-text">Edit Image</div>  
                                </div>
                                <div class="my_custom_post_type_category_image_box_show mt-3">
                                    <?php if($value):?>
                                        <img src="<?php echo esc_url(wp_get_attachment_url($value)) ?>" alt="amcpt-image">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded no_image_box" style="width: 100%; height: 100px">No Image</div>
                                    <?php endif; ?>
                                    <div class="my_custom_post_type_category_image_remove_image">
                                        X
                                    </div>
                                    <label class="p-0 text-muted">(Edit Image for <?php echo esc_html($this->taxonomy_slug);?>)</label>
                                </div>
                                    
                            </div>
                        </td>
                    </tr>
                <?php       
        } 

    /* <======== Add Image Column for Category Fields=============> */
        public function my_custom_post_type_add_new_column( $columns ){
            $columns['my_custom_post_type_category_image_id'] = esc_html__('Image', "my-custom-post-type");
            return $columns;
        }
     // Add info to the new columns 
        public function my_custom_post_type_add_info_to_custom_columns( $string, $columns, $term_id ) {
            $tax_img_id = get_term_meta( $term_id, $this->taxonomy_slug.'_my_custom_post_type_image_id', true );
            switch ( $columns ) {
                case 'my_custom_post_type_category_image_id':
                    ?>
                        <?php if($tax_img_id):?>
                         <img src="<?php echo esc_url(wp_get_attachment_url( $tax_img_id, 100, 100)) ?>" alt= <?php echo esc_attr($this->taxonomy_slug."_img");?> style="width: 100px">
                        <?php else: ?>
                            <div class="bg-secondary d-flex align-items-center justify-content-center rounded text-white" style="width: 100px; height: 80px">No Image</div>
                        <?php endif; ?>
                    <?php
                break;
            }
        ?>
            <script>
                jQuery(document).ready(function($){         
                    $(".my_custom_post_type_category_image_box_show").find("img").attr("src", "");
                    $(".my_custom_post_type_category_image_box_show").css({"display":"none"});
                    $(".my_custom_post_type_category_image_box").css({"display":"flex"});
                    $("#my_custom_post_type_category_image_id").val("");       
                });
            </script>
        <?php
        } 
        
        public function my_custom_post_type_created_tax_image( $term_id ) {
            update_term_meta( $term_id, $this->taxonomy_slug.'_my_custom_post_type_image_id', sanitize_text_field( $_POST['my_custom_post_type_category_image_id'] ) );
        }

        public function my_custom_post_type_edited_tax_image( $term_id ) {
            update_term_meta( $term_id, $this->taxonomy_slug.'_my_custom_post_type_image_id', sanitize_text_field( $_POST['my_custom_post_type_category_image_id'] ) );
        }    



    }
    
}

new My_Custom_Post_Type();