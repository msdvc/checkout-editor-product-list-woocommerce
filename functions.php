/*
 * Questa funzione invece, crea la delete function!
 */
function webroom_add_delete_and_quantity_on_checkout( $product_title, $cart_item, $cart_item_key ) {

/* Zona checkout */
if (  is_checkout() ) {
    /* Get Cart of the user */
    $cart     = WC()->cart->get_cart();
        foreach ( $cart as $cart_key => $cart_value ){
           if ( $cart_key == $cart_item_key ){
                $product_id = $cart_item['product_id'];
                $_product   = $cart_item['data'] ;
                
                /* Step 1 : Add delete icon */
                $return_value = sprintf(
                  '<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                  esc_url( wc_get_cart_remove_url( $cart_key ) ),
                  __( 'Delete', 'woocommerce' ),
                  esc_attr( $product_id ),
                  esc_attr( $_product->get_sku() )
                );
                
                /* Step 2 : Add product name */
                $return_value .= '&nbsp; <span class = "product_name" >' . $product_title . '</span>' ;
                
                /* Step 3 : Add quantity selector */
                if ( $_product->is_sold_individually() ) {
                  $return_value .= sprintf( ' <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_key );
                } else {
                  $return_value .= woocommerce_quantity_input( array(
                      'input_name'  => "cart[{$cart_key}][qty]",
                      'input_value' => $cart_item['quantity'],
                      'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                      'min_value'   => '1'
                      ), $_product, false );
                }
                return $return_value;
            }
        }
}else{
    /*
     * Questa funzione stampa il nome del prodotto
     * Sia nel carrello sia nel checkout :)
     */
    $_product   = $cart_item['data'] ;
    $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
    if ( ! $product_permalink ) {
        $return_value = $_product->get_title() . '&nbsp;';
    } else {
        $return_value = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title());
    }
    return $return_value;
  }
}

add_filter ('woocommerce_cart_item_name', 'webroom_add_delete_and_quantity_on_checkout' , 10, 3 );


/* Funzione che richiama il javascript inserito nel template footer.php del tema */
function webroom_add_quanity_js(){
  if ( is_checkout() ) {
	wp_enqueue_script( 'checkout_script', get_stylesheet_directory_uri() . '/js/add_quantity.js', '', '', false );
    $localize_script = array(
      'ajax_url' => admin_url( 'admin-ajax.php' )
    );
    wp_localize_script( 'checkout_script', 'add_quantity', $localize_script );
  }
}
add_action( 'wp_footer', 'webroom_add_quanity_js', 10 );
function webroom_load_ajax() {
  if ( !is_user_logged_in() ){
      add_action( 'wp_ajax_nopriv_update_order_review', 'webroom_update_order_review' );
  } else{
      add_action( 'wp_ajax_update_order_review', 'webroom_update_order_review' );
  }
}
add_action( 'init', 'webroom_load_ajax' );
function webroom_update_order_review() {
  $values = array();
  parse_str($_POST['post_data'], $values);
  $cart = $values['cart'];
  foreach ( $cart as $cart_key => $cart_value ){
      WC()->cart->set_quantity( $cart_key, $cart_value['qty'], false );
      WC()->cart->calculate_totals();
      woocommerce_cart_totals();
  }
  wp_die();
}
