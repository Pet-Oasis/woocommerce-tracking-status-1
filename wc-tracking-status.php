<?php
/*
Plugin Name: WooCommerce Order Status Change time tracking
Plugin URI: https://petoasisksa.com
Description: This plugin allows you to track the WooCommerce orders status changes, select status types from the plugin options and it will automatically store the date and time for each status change and it will automatically calculate the time between these statuses in Days, Hours, and minutes
Author: Saleem Summour
Version: 1.0.0
Author URI: https://lvendr.com/
*/

/*
* Show order change statues date menu in the dashboard
*/
function order_status_date_menu() {
    add_menu_page( 'Order status', 'Order status', 'manage_options', 'order-status-id', 'order_status_date_options','',6 );
    add_submenu_page( 'order-status-id', 'Order status Setting', 'Order status Setting', 'manage_options', 'order_status_settings','order_status_setting_display');

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
    include_once( plugin_dir_path( __FILE__ ) . 'tracking_table_class.php' );
    $tracking_table = new tracking_status_List_Table();
    $tracking_table->prepare_items();

    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h2>WooCommerce Order Status Change time tracking</h2>
        <?php $tracking_table->display(); ?>
    </div>
    <?php

}

/*
 * date for order status settings
 */
function order_status_setting_display(){
    $order_status1=get_option('order_status1');
    $order_status2=get_option('order_status2');
    $order_status3=get_option('order_status3');
    $order_status_calculate=get_option('order_status_calculate');

    if(isset($_POST['submit'])){
        if(isset($_POST['order_status1'])){
            update_option('order_status1',$_POST['order_status1'],'');
        }
        if(isset($_POST['order_status2'])){
            update_option('order_status2',$_POST['order_status2'],'');
        }
        if(isset($_POST['order_status3'])){
            update_option('order_status3',$_POST['order_status3'],'');
        }
        if(isset($_POST['order_status_calculate'])){
            update_option('order_status_calculate',$_POST['order_status_calculate'],'');
        }

    }
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h2>Orders status time tracking</h2>
        <form method="post"  novalidate="novalidate">
            <table class="form-table" role="presentation">

                <tbody>
                <tr>
                    <th scope="row"><label for="order_status1">Order Status1</label></th>
                    <td>
                        <select name="order_status1" id="order_status1">
                            <option></option>
                            <?php
                            foreach (wc_get_order_statuses() as $status_key=>$status_value){
                                echo '<option value="'.$status_key.'" '.($status_key==$order_status1 ? 'selected' : '').'>'.$status_value.'</option>';
                            }

                            ?>
                        </select>
                    </td>

                </tr>
                <tr>
                    <th scope="row"><label for="order_status2">Order Status2</label></th>
                    <td>
                        <select name="order_status2" id="order_status2">
                            <option></option>
                            <?php
                            foreach (wc_get_order_statuses() as $status_key=>$status_value){
                                echo '<option value="'.$status_key.'" '.($status_key==$order_status2 ? 'selected' : '').'>'.$status_value.'</option>';

                            }

                            ?>
                        </select>
                    </td>

                </tr>
                <tr>
                    <th scope="row"><label for="order_status3">Order Status3</label></th>
                    <td>
                        <select name="order_status3" id="order_status3">
                            <option></option>
                            <?php
                            foreach (wc_get_order_statuses() as $status_key=>$status_value){
                                echo '<option value="'.$status_key.'" '.($status_key==$order_status3 ? 'selected' : '').'>'.$status_value.'</option>';
                            }

                            ?>
                        </select>
                    </td>

                </tr>
                <tr>
                    <th scope="row">Calculate by</th>
                    <td>
                        <fieldset>
                            <label for="order_status_calculate_days">
                                <input name="order_status_calculate[]" <?php echo (in_array('days',$order_status_calculate) ? ' checked="checked"' : '');?> type="checkbox" id="order_status_calculate_days" value="days">
                                Days
                            </label>
                            <label for="order_status_calculate_hours">
                                <input name="order_status_calculate[]" <?php echo (in_array('hours',$order_status_calculate) ? ' checked="checked"' : '');?>  type="checkbox" id="order_status_calculate_hours" value="hours">
                                Hours
                            </label>
                            <label for="order_status_calculate_minutes">
                                <input name="order_status_calculate[]" <?php echo (in_array('minutes',$order_status_calculate) ? ' checked="checked"' : '');?>  type="checkbox" id="order_status_calculate_minutes" value="minutes">
                                Minutes
                            </label>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>


            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>

    <?php
}

/*
 * Register Options for the first time
 */
function register_status_setting_options() {

    if ( !get_option('order_status1') ){
        add_option('order_status1','processing','','yes');
    }
    if ( !get_option('order_status2') ){
        add_option('order_status2','dispatch','','yes');
    }
    if ( !get_option('order_status3') ){
        add_option('order_status3','completed','','yes');
    }
    if ( !get_option('order_status_calculate') ){
        add_option('order_status_calculate','days','','yes');
    }

}
//add_action( 'init', 'register_status_setting_options' );

/*
 * update the date for order status
 */
add_action( 'woocommerce_thankyou', 'update_order_processing_date', 20 );
function update_order_processing_date ( $order_id )
{
    $order_status1=get_option('order_status1');
    $order_status2=get_option('order_status2');
    $order_status3=get_option('order_status3');
    $order_status_calculate=get_option('order_status_calculate');

    $order = wc_get_order($order_id);
    $order_status = $order->get_status();

    if ( $order_status == substr($order_status1,3) && empty(get_post_meta($order_id,'order_status1',true))) {
        update_post_meta($order_id,'order_status1',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status== substr($order_status2,3) && empty(get_post_meta($order_id,'order_status2',true))){
        update_post_meta($order_id,'order_status2',current_time( 'Y-m-d H:i:s' ));
    }
    if($order_status== substr($order_status3,3) && empty(get_post_meta($order_id,'order_status3',true))){
        update_post_meta($order_id,'order_status3',current_time( 'Y-m-d H:i:s' ));
    }
}
/*
* update the date for order status
*/

function action_woocommerce_order_status_changed( $order_id, $old_status, $new_status, $order ) {
    $order_status1=get_option('order_status1');
    $order_status2=get_option('order_status2');
    $order_status3=get_option('order_status3');
    $order_status_calculate=get_option('order_status_calculate');


    if ( $new_status == substr($order_status1,3) && empty(get_post_meta($order_id,'order_status1',true))) {
        update_post_meta($order_id,'order_status1',current_time( 'Y-m-d H:i:s' ));
    }
    if($new_status==substr($order_status2,3) && empty(get_post_meta($order_id,'order_status2',true))){
        update_post_meta($order_id,'order_status2',current_time( 'Y-m-d H:i:s' ));
    }
    if($new_status==substr($order_status3,3) && empty(get_post_meta($order_id,'order_status3',true))){
        update_post_meta($order_id,'order_status3',current_time( 'Y-m-d H:i:s' ));
    }

}
add_action( 'woocommerce_order_status_changed', 'action_woocommerce_order_status_changed', 10, 4 );