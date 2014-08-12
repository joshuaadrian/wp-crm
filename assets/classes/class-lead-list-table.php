<?php

class Lead_List_Table extends WP_List_Table {

  /**
  * Constructor, we override the parent to pass our own arguments
  * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
  */
  function __construct() {
    parent::__construct( array(
      'singular' => 'wp_lead_list', //Singular label
      'plural'   => 'wp_lead_lists', //plural label, also this well be one of the table css class
      'ajax'     => false, //We won't support Ajax for this table
      'screen'   => 'wp-crm' //hook suffix
    ) );
  }

  function extra_tablenav( $which ) {

    if ( $which == "top" ) {
      //The code that goes before the table is here
      echo "Hello, I'm before the table";
    }

    if ( $which == "bottom" ) {
      //The code that goes after the table is there
      echo "Hi, I'm after the table";
    }

  }

  function get_columns() {
    return $columns = array(
      'col_lead_id'   => __('ID'),
      'col_lead_time' => __('Time'),
      'col_lead_name' => __('Name'),
      'col_lead_text' => __('Text'),
      'col_lead_url'  => __('Url')      
    );
  }

  public function get_sortable_columns() {
    return $sortable = array(
      'col_lead_id'      => 'id',
      'col_lead_name'    => 'name'
    );
  }

  function prepare_items() {
    
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();

    $query = "SELECT * FROM " . $wpdb->prefix . "crm_leads"; // Preparing your query

    /* -- Ordering parameters -- */
    //Parameters that are going to be used to order the result
    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
    $order   = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';

    if ( !empty( $orderby ) & !empty( $order ) ) {
      $query .= ' ORDER BY '.$orderby.' '.$order;
    }

    $totalitems = $wpdb->query($query); //return the total number of affected rows

    $perpage = 20;
    //Which page is this?
    $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
    //Page Number
    if ( empty($paged) || !is_numeric($paged) || $paged<=0 ) {
      $paged=1;
    }

    $totalpages = ceil($totalitems/$perpage);

    if ( !empty( $paged ) && !empty( $perpage ) ) {
      $offset=($paged-1)*$perpage;
      $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
    }

    /* -- Register the pagination -- */
    $this->set_pagination_args( array(
      "total_items" => $totalitems,
      "total_pages" => $totalpages,
      "per_page"    => $perpage,
    ) );
    //The pagination links are automatically built according to those parameters

    /* -- Register the Columns -- */
    $columns = $this->get_columns();
    $_wp_column_headers['wp-crm'] = $columns;

    /* -- Fetch the items -- */
    $this->items = $wpdb->get_results($query);

  }

  function display_rows() {

    $records = $this->items; //Get the records registered in the prepare_items method

    list( $columns, $hidden ) = $this->get_column_info(); //Get the columns registered in the get_columns and get_sortable_columns methods

     //Loop for each record
    if ( !empty( $records ) ) {

      $rec_counter = 1;

      foreach ( $records as $rec ) {

        $alt = $rec_counter % 2 ? 'alternate' : '';

        echo '<tr id="record_'.$rec->link_id.'" class="' . $alt . '">'; //Open the line
        
        foreach ( $columns as $column_name => $column_display_name ) {

          //Style attributes for each col
          $class = "class='$column_name column-$column_name'";
          $style = "";

          if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';

          $attributes = $class . $style;

          //edit link
          $editlink  = '/wp-admin/lead.php?action=edit&lead_id='.(int)$rec->id;

          //Display the cell
          switch ( $column_name ) {
            case "col_lead_id" :  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
            case "col_lead_time" : echo '<td '.$attributes.'>'.$rec->time.'</td>'; break;
            case "col_lead_name" : echo '<td '.$attributes.'>'.stripslashes($rec->name).'5</td>'; break;
            case "col_lead_text" : echo '<td '.$attributes.'>'.$rec->text.'</td>'; break;
            case "col_lead_url" : echo '<td '.$attributes.'>'.stripslashes($rec->url).'</td>'; break;
          }
        
        }

        //Close the line
        echo'</tr>';

        $rec_counter++;

      }

    }

  }

}