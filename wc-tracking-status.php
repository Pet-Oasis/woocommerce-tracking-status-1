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
    <h2>Product quantity from order</h2>

    <?php
    $orders = wc_get_orders( array(
        'limit'        => -1, // Query all orders
        'orderby'      => 'date',
        'order'        => 'DESC',
        'meta_key'     => 'processing_date', // The postmeta key field
        'meta_value'     => null, // The postmeta key value
        'meta_compare' => '!=', // The comparison argument
    ));
    ?>

    <table>
        <thead>
        <tr>
            <th>Order ID</th>
            <th>Processing Date</th>
            <th>Dispatch Date</th>
            <th>Completed Date</th>
            <th>Processing to Dispatch</th>
            <th>Processing to Completed</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($orders as $order){
            $processing_date=get_post_meta($order->get_id(),'processing_date',true);
            $dispatch_date=get_post_meta($order->get_id(),'dispatch_date',true);
            $completed_date=get_post_meta($order->get_id(),'completed_date',true);

            echo "<tr>";
            echo "<td>".$order->get_id()."</td>";
            echo "<td>".$processing_date."</td>";
            echo "<td>".$dispatch_date."</td>";
            echo "<td>".$completed_date."</td>";
            $processingStamp = strtotime($processing_date);
            $dispatchStamp = strtotime($dispatch_date);
            $completedStamp = strtotime($completed_date);
            $numberDays_dispatch = intval(abs($processingStamp - $dispatchStamp)/86400);
            $numberDays_completed = intval(abs($processingStamp - $completedStamp)/86400);
            echo "<td>".$numberDays_dispatch." Days</td>"; // use for point out relation: smaller/greater
            echo "<td>".$numberDays_completed." Days</td>"; // use for point out relation: smaller/greater
            echo "</tr>";

        }
        ?>
        <td></td>
        </tbody>
    </table>
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
    if ($order_status == 'processing') {
        update_post_meta($order_id, 'processing_date', date("Y/m/d"));
    }
    if ($order_status == 'dispatch-ready') {
        update_post_meta($order_id, 'dispatch_date', date("Y/m/d"));
    }
    if ($order_status == 'completed') {
        update_post_meta($order_id, 'completed_date', date("Y/m/d"));

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

    if ( $order_status == 'processing') {
        update_post_meta($post_id,'processing_date',date("Y/m/d"));
    }
    if($order_status=='dispatch-ready'){
        update_post_meta($post_id,'dispatch_date',date("Y/m/d"));
    }
    if($order_status=='completed'){
        update_post_meta($post_id,'completed_date',date("Y/m/d"));
    }

}
