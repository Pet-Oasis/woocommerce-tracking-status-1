<?php
/*
Plugin Name: Woocommerce tracking order status date
Plugin URI: https://petoasisksa.com
Description: Track order status change date.
Author: Saleem Summour
Version: 1.0.0
Author URI: https://lvendr.com/
*/

/*
* Show order change statues date menu in the dashboard
*/
function order_status_date_menu() {
    add_menu_page( 'Order status', 'Order status', 'manage_options', 'order-status-id', 'order_status_date_options','',6 );

}
add_action( 'admin_menu', 'order_status_date_menu');

/*
 * Show follow up menu function
 */
function order_status_date_options(){
    order_status_date_display();
}
/*
 * date for order status menu
 */
function order_status_date_display(){
    ?>
    <?php
    $exampleListTable = new Example_List_Table();
    $exampleListTable->prepare_items();
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h2>Woocommerce tracking order status date</h2>
        <?php $exampleListTable->display(); ?>
    </div>
    <?php

}

/*
 * update the date for order status
 */
add_action( 'woocommerce_thankyou', 'update_order_processing_date', 20 );
function update_order_processing_date ( $order_id )
{
    $order = wc_get_order($order_id);
    $order_status = $order->get_status();
    if ( $order_status == 'processing' && empty(get_post_meta($order_id,'processing_date',true))) {
        update_post_meta($order_id,'processing_date',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status=='dispatch-ready' && empty(get_post_meta($order_id,'dispatch_date',true))){
        update_post_meta($order_id,'dispatch_date',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status=='completed' && empty(get_post_meta($order_id,'completed_date',true))){
        update_post_meta($order_id,'completed_date',current_time( 'Y-m-d H:i:s' ));
    }
}
/*
     * update the date for order status
     */
add_action( 'save_post', 'update_order_status_date_dashboard', 10, 3 );
function update_order_status_date_dashboard( $post_id, $post, $update ){

    // Orders in backend only
    if( ! is_admin() ) return;
    if ( 'shop_order' !== $post->post_type ) {
        return;
    }
    // Get an instance of the WC_Order object (in a plugin)
    $order = wc_get_order( $post_id );
    $order_status  = $order->get_status();

    if ( $order_status == 'processing' && empty(get_post_meta($post_id,'processing_date',true))) {
        update_post_meta($post_id,'processing_date',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status=='dispatch-ready' && empty(get_post_meta($post_id,'dispatch_date',true))){
        update_post_meta($post_id,'dispatch_date',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status=='completed' && empty(get_post_meta($post_id,'completed_date',true))){
        update_post_meta($post_id,'completed_date',current_time( 'Y-m-d H:i:s' ));
    }

}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class Example_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'id'          => 'ID',
            'processing_date'       => 'Processing Date',
            'dispatch_date' => 'Dispatch Date',
            'completed_date'        => 'Completed Date',
            'processing_dispatch'    => 'Processing to Dispatch',
            'processing_completed'      => 'Processing to Completed'
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $orders = wc_get_orders( array(
            'limit'        => -1, // Query all orders
            'orderby'      => 'date',
            'order'        => 'DESC',
            'meta_key'     => 'processing_date', // The postmeta key field
            'meta_value'     => null, // The postmeta key value
            'meta_compare' => '!=', // The comparison argument
        ));
        $data = array();

        foreach ($orders as $order){
            $processing_date=get_post_meta($order->get_id(),'processing_date',true);
            $dispatch_date=get_post_meta($order->get_id(),'dispatch_date',true);
            $completed_date=get_post_meta($order->get_id(),'completed_date',true);

            $processing_date1 = new DateTime($processing_date);

            if(!empty($dispatch_date)){
                $dispatch_date1 = new DateTime($dispatch_date);
                $numberDays_dispatch = $dispatch_date1->diff($processing_date1);
                $numberDays_dispatch1=$numberDays_dispatch->format('%a Day and %h hours and %i minutes');

            }
            else{
                $numberDays_dispatch1="";
            }
            if(!empty($completed_date)){
                $completed_date1 = new DateTime($completed_date);
                $numberDays_completed = $completed_date1->diff($processing_date1);
                $numberDays_completed1 =$numberDays_completed->format('%a Day and %h hours and %i minutes');
            }
            else{
                $numberDays_completed1="";
            }

            $data[] = array(
                'id'          => $order->get_id(),
                'processing_date'       => $processing_date,
                'dispatch_date' => $dispatch_date,
                'completed_date'        => $completed_date,
                'processing_dispatch'    => $numberDays_dispatch1,
                'processing_completed'      => $numberDays_completed1
            );


        }





        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'processing_date':
            case 'dispatch_date':
            case 'completed_date':
            case 'processing_dispatch':
            case 'processing_completed':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'processing_date';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
