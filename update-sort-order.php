<?php
/*
Plugin Name: Auto update sort order
Description: Automate the update of the sort order of Wordpress posts
Author: Yarno van Oort<ik@yarnovanoort.nl>
Version: 0.1
Author URI: https://www.yarnovanoort.nl
Text Domain: update-sort-order
*/

if (!class_exists('update_sort_order')) {

    class update_sort_order
    {
        protected $article;
        protected $qa;
        protected $herinneringen;

        protected $article_post_amount;
        protected $qa_post_amount;
        protected $herinneringen_post_amount;

        public function __construct()
        {
            $this->article_post_amount = 1;
            $this->qa_post_amount = 2;
            $this->herinneringen_post_amount = 1;

            add_action( 'pre_get_posts', array($this, 'getCurrentPosts'), 99, 2);
        }

        public function getCurrentPosts($query)
        {
            if ( $query->is_home() && $query->is_main_query() && ! is_admin() ) {
                $args = [];
                $tlom_posts = get_posts( $args );

                $tlom_arr = $this->getCategoryByPost($tlom_posts);

                $reorder_posts = $this->tlom_update_post_order($tlom_arr);

                $query->set( 'orderby', 'menu_order' );
                $query->set( 'order', 'ASC');
            }

        }

        private function getCategoryByPost($tlom_posts)
        {
            $tlom_post_info = [];

            foreach ($tlom_posts as $tlom_post) {
                $tlom_post_info[] = [
                    'ID' => $tlom_post->ID,
                    'menu_order' => $tlom_post->menu_order,
                    'category_id' => wp_get_post_categories( $tlom_post->ID )
                ];

            }

            return $tlom_post_info;
        }

        private function tlom_update_post_order($tlom_posts)
        {
            /*  We need to reshuffle the array in the right order like sony wants YvO
             *  Order: Article, QA, QA, Herinnering YvO
             */

            $update_arr = [];

            $article_int = 0;
            $qa_int = 1;
            $herinnering_int = 3;

            foreach ($tlom_posts as $tlom_post) {

                if ($tlom_post['category_id'][0] === 2) {

                    $update = $this->tlom_update_post($tlom_post['ID'], $article_int);

                    $article_int = $article_int + 4;
                }

                if($tlom_post['category_id'][0] === 3) {
                    $update = $this->tlom_update_post($tlom_post['ID'], $qa_int);

                    $qa_int = $qa_int + 1;
                }

                if($tlom_post['category_id'][0] === 4) {
                    $update = $this->tlom_update_post($tlom_post['ID'], $herinnering_int);

                    $herinnering_int = $herinnering_int + 3;
                }
            }

            return;
        }

        private function tlom_update_post($post_id, $menu_order)
        {
            wp_update_post( array( 'ID' => $post_id, 'post_title' => $menu_order, 'menu_order' =>  $menu_order) );

            return true;
        }

    }

    new update_sort_order();
}

