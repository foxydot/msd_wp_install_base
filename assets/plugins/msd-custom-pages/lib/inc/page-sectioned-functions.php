<?php
class MSDSectionedPage{
    /**
         * A reference to an instance of this class.
         */
        private static $instance;


        /**
         * Returns an instance of this class. 
         */
        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new MSDSectionedPage();
                } 

                return self::$instance;

        } 
        
        /**
         * Initializes the plugin by setting filters and administration functions.
         */
        private function __construct() {
            add_action('admin_footer',array(&$this,'info_footer_hook') );            
        }
        
    function add_metaboxes(){
        global $post,$sectioned_page_metabox,$wpalchemy_media_access;
        $sectioned_page_metabox = new WPAlchemy_MetaBox(array
        (
            'id' => '_sectioned_page',
            'title' => 'Page Sections',
            'types' => array('page'),
            'context' => 'normal', // same as above, defaults to "normal"
            'priority' => 'high', // same as above, defaults to "high"
            'template' => WP_PLUGIN_DIR.'/'.plugin_dir_path('msd-custom-pages/msd-custom-pages.php'). '/lib/template/metabox-sectioned-page.php',
            'autosave' => TRUE,
            'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
            'prefix' => '_msdlab_', // defaults to NULL
            //'include_template' => 'sectioned-page.php',
        ));
    }
    
    function sectioned_page_output(){
        wp_enqueue_script('sticky',WP_PLUGIN_URL.'/'.plugin_dir_path('msd-custom-pages/msd-custom-pages.php'). '/lib/js/jquery.sticky.js',array('jquery'),FALSE,TRUE);
        wp_enqueue_script('jquery-path',WP_PLUGIN_URL.'/'.plugin_dir_path('msd-custom-pages/msd-custom-pages.php'). '/lib/js/jquery.path.js',array('jquery','bootstrap-jquery'));
        
        global $post,$subtitle_metabox,$sectioned_page_metabox,$nav_ids;
        $i = 1;
        if(is_object($sectioned_page_metabox)){
        while($sectioned_page_metabox->have_fields('sections')){
            $eo = $i%2==1?'even':'odd';
            $pull = $i%2==1?'left':'right';
            $title = apply_filters('the_title',$sectioned_page_metabox->get_the_value('title'));
            $wrapped_title = trim($title) != ''?'<div class="section-title">
        <h3 class="wrap">
            '.$title.'
        </h3>
    </div>':'';
            $slug = sanitize_title_with_dashes(str_replace('/', '-', $sectioned_page_metabox->get_the_value('title')));
            $subtitle = $sectioned_page_metabox->get_the_value('subtitle') !=''?'<h4 class="section-subtitle">'.apply_filters('the_content',$sectioned_page_metabox->get_the_value('subtitle')).'</h4>':'';
            $content = apply_filters('the_content',$sectioned_page_metabox->get_the_value('content'));
            $image = $sectioned_page_metabox->get_the_value('image') !=''?'<img src="'.$sectioned_page_metabox->get_the_value('image').'" class="pull-'.$pull.'">':'';
            $nav_ids[] = $slug;
            $nav[] = '';
            $billboard_nav[] = '<a id="'.$slug.'_bb_nav" href="#'.$slug.'" class="nav-icon-'.$i.'"><div class="round-wrap"><i class="fa-3x adex-'.$slug.'"></i></div>'.str_replace(' ', '<br>', $title).'</a>';
            $floating_nav[] = '<a id="'.$slug.'_fl_nav" href="#'.$slug.'"><i class="fa-3x adex-'.$slug.'"></i>'.str_replace(' ', '<br>', $title).'</a>';
            $sections[] = '
<div id="'.$slug.'" class="section section-'.$eo.' section-'.$slug.' clearfix">
    '.$wrapped_title.'
    <div class="section-body">
        <div class="wrap">
            '.$image.'
            '.$subtitle.'
            '.$content.'
        </div>
    </div>
</div>
';
            $i++;
        }//close while
        if(!is_front_page()){
            print '<div id="billboard_nav" class="billboard_nav image-widget-background" style="background-image:url('.msdlab_get_thumbnail_url($post->ID,'page_banner').');">
            <div class="wrap">
            <h1 class="page-title">'.$post->post_title.'</h1>
            <div class="sep even"></div>
            <div class="fuzzybubble">
                <div class=""> 
                <h3 class="entry-subtitle">'.$subtitle_metabox->get_the_value('subtitle').'</h3>
                <p class="">'.apply_filters('the_content',$post->post_content).'</p>
                </div>
            </div>
            <div class="nav-icons-wrapper">'.implode("\n",$billboard_nav).'</div>
            </div>
            </div>';
            print '<div id="floating_nav" class="floating_nav">'.implode("\n",$floating_nav).'</div>';
        }
        print implode("\n",$sections);
        
        }//clsoe if
    }

    function sectioned_page_floating_nav(){
        global $nav_ids; //http://julian.com/research/velocity/ llook at this to speed up animations
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#floating_nav").sticky({ topSpacing: 0 });
            /*$("#billboard_nav .fuzzybubble").blurjs({
                radius: 10,
                source: $('.image-widget-background'), 
                });*/
            var arc_params = function(i) {
              var arc_positions = [240,270,300,60,90,120];
              return new $.path.arc({
                center: [230,100],  
                    radius: 320,    
                    start: 180,
                    end: arc_positions[i],
                    dir: -1
              });
            }
            <?php
            $i = 0;
            foreach($nav_ids AS $nav_id){
                print '$("#billboard_nav #'.$nav_id.'_bb_nav").delay('.($i*500).').animate({opacity: 1,path : arc_params('.$i.')},4000);
                ';
                $i++;
            }
            ?>
        });
        </script>
        <?php
    }
        function info_footer_hook()
        {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                ?><script type="text/javascript">
                        jQuery('#postdivrich').after(jQuery('#_sectioned_page_metabox'));
                    </script><?php
            }
        }
}