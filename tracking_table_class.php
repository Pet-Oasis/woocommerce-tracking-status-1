<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class tracking_status_List_Table extends WP_List_Table
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
        $order_status1=get_option('order_status1');
        $order_status2=get_option('order_status2');
        $order_status3=get_option('order_status3');

        $columns = array(
            'id'          => 'ID',
            'order_status1'       => substr($order_status1,3).' Date',
            'order_status2' => substr($order_status2,3).' Date',
            'order_status3'        => substr($order_status3,3).' Date',
            'order_status1to2'    => substr($order_status1,3).' to '.substr($order_status2,3),
            'order_status1to3'      => substr($order_status1,3).' to '.substr($order_status3,3)
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
        $order_status1=get_option('order_status1');
        $order_status2=get_option('order_status2');
        $order_status3=get_option('order_status3');
        $order_status_calculate=get_option('order_status_calculate');

        $orders = wc_get_orders( array(
            'limit'        => -1, // Query all orders
            'orderby'      => 'date',
            'order'        => 'DESC',
            'meta_key'     => substr($order_status1,3).'_time', // The postmeta key field
            'meta_value'     => null, // The postmeta key value
            'meta_compare' => '!=', // The comparison argument
        ));
        $data = array();

        foreach ($orders as $order){
            $order_status1_meta=get_post_meta($order->get_id(),substr($order_status1,3).'_time',true);
            $order_status2_meta=get_post_meta($order->get_id(),substr($order_status2,3).'_time',true);
            $order_status3_meta=get_post_meta($order->get_id(),substr($order_status3,3).'_time',true);

            $order_status1_date = new DateTime($order_status1_meta);

            if(!empty($order_status2_meta)){
                $order_status2_date = new DateTime($order_status2_meta);
                $numberDays1to2 = $order_status2_date->diff($order_status1_date);
                $numberDays1to2d=$numberDays1to2->format('%a Day and %h hours and %i minutes');

            }
            else{
                $numberDays1to2d="";
            }
            if(!empty($order_status3_meta)){
                $order_status3_date = new DateTime($order_status3_meta);
                $numberDays2to3 = $order_status3_date->diff($order_status1_date);
                $numberDays2to3d =$numberDays2to3->format('%a Day and %h hours and %i minutes');
            }
            else{
                $numberDays2to3d="";
            }

            $data[] = array(
                'id'          => $order->get_id(),
                'order_status1'       => $order_status1_meta,
                'order_status2' => $order_status2_meta,
                'order_status3'        => $order_status3_meta,
                'order_status1to2'    => $numberDays1to2d,
                'order_status1to3'      => $numberDays2to3d
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
            case 'order_status1':
            case 'order_status2':
            case 'order_status3':
            case 'order_status1to2':
            case 'order_status1to3':
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
        $orderby = 'order_status1';
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
