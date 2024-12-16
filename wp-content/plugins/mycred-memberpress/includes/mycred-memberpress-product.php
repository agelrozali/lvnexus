<?php
if ( ! defined( 'mycred_memberpress_SLUG' ) ) exit;

/**
 * mycred_memberpress_Addons_Module class
 * @since 0.1
 * @version 1.1.1
 */

if(!class_exists('MycredMembr_PurchaseASpecificSubsProduct')):
    class MycredMembr_PurchaseASpecificSubsProduct extends myCRED_Hook {

        /**
         * Construct
         * @param $hook_prefs
         * @param string $type
         */
        function __construct( $hook_prefs, $type = 'mycred_default' ) {
            parent::__construct( array(
                'id'       => 'specific_product',
                'defaults' => array(
                    'creds'   => 1,
                    'log'     => '%plural% for Purchasing Subscription Product',
                    'limit'   => 'x',
                    'product_recurring'   => 'recurring_checkbox',
                    'recurring-creds'     => 1,
                    'recurring-log'     => '%plural% for Purchasing OneTime or Recurring Product',
                    'mycred_check_addmore' => '1',
                    'memberpress_product' => array(
                        'creds'                         => array(),
                        'log'                           => array(),
                        'product'                       => array(),
                        'specific_product_recurring'    => array(),
                        'specific-recurring-creds'      => array(),
                        'specific-recurring-log'        => array()
                        
                    ),
                )
            ), $hook_prefs, $type );
            
        }

        /**
         * Run
         * @since 0.1
         * @version 1.1
         */
        public function run() {

            // WordPress
            add_action( 'mepr-event-transaction-completed', array( $this, 'purchased_specif_product' ), 10 );

        }

        /**
         * Login Hook
         * @since 0.1
         * @version 1.3
         */

        public function purchased_specif_product( $event ) {

            $prefs = $this->prefs;
            $transaction = $event->get_data();
            $user_ID = $transaction->rec->user_id;
            $member_user = $transaction;

            $user_subscription = $member_user->product_id;

            // Check for exclusion
            if ( $this->core->exclude_user( $user_ID ) ) return;

            // Limit
            if ( ! $this->over_hook_limit( 'memberpress_product', 'memberpress_product', $user_ID ) ) {
                
                if( $prefs['mycred_check_addmore'] == 1 && in_array( $user_subscription, $prefs['memberpress_product']['product'] ) ) {

                    $hook_index = array_search( $user_subscription, $prefs['memberpress_product']['product']  );
                  
                    if ( $hook_index === false ) {
                        
                        foreach ( $prefs['memberpress_product']['product'] as $key => $value ) {
                            
                            $hook_index = $key;
                        
                        }

                    }

                    if ( 
                        !empty( $prefs['memberpress_product']['creds'] ) && 
                        isset( $prefs['memberpress_product']['creds'][$hook_index] ) && 
                        !empty( $prefs['memberpress_product']['log'] ) && 
                        !empty( $prefs['memberpress_product']['log'][$hook_index] ) 
                    ) {
                    
                        $this->core->add_creds(
                            'memberpress_product',
                            $user_ID,
                            $prefs['memberpress_product']['creds'][$hook_index],
                            $prefs['memberpress_product']['log'][$hook_index],
                            0,
                            '',
                            $this->mycred_type
                        );

                    }

                } 
                else {
                
                    $this->core->add_creds(
                        'memberpress_product',
                        $user_ID,
                        $prefs['creds'],
                        $prefs['log'],
                        0,
                        '',
                        $this->mycred_type
                    );
                
                }

            }
            
            if( $prefs['mycred_check_addmore'] == 1 ) {

                $is_first_real_payment = ( $transaction->amount > 0.00 );
                
                if ( $is_first_real_payment ) {

                    $hook_index = array_search( $user_subscription , $prefs['memberpress_product']['product']  );

                    if ( $hook_index === false ) {
                        
                        foreach ( $prefs['memberpress_product']['product'] as $key => $value ) {
                            
                            $hook_index = $key;

                        }

                    }

                    if ( 
                        $prefs['memberpress_product']['specific_product_recurring'][$hook_index] == '1' && 
                        !empty( $prefs['memberpress_product']['specific-recurring-creds'] ) && 
                        isset( $prefs['memberpress_product']['specific-recurring-creds'][$hook_index] ) && 
                        !empty( $prefs['memberpress_product']['specific-recurring-log'] ) && 
                        !empty( $prefs['memberpress_product']['specific-recurring-log'][$hook_index] ) 
                    ) {

                        // Check for exclusion
                        if ( $this->core->exclude_user( $user_ID ) ) return;
                        
                        $this->core->add_creds(
                            'product_recurring',
                            $user_ID,
                            $prefs['memberpress_product']['specific-recurring-creds'][$hook_index],
                            $prefs['memberpress_product']['specific-recurring-log'][$hook_index],
                            0,
                            '',
                            $this->mycred_type
                        );

                    }

                }

            } 
            else {

                if( $prefs['product_recurring'] == 'recurring' ) {

                    $member_user = $transaction;

                    $subscription = $transaction->subscription();

                    $is_first_real_payment = false;

                    if( $subscription !== false ) {
                       
                        if( $subscription->trial && $subscription->trial_amount <= 0.00 && $subscription->txn_count == 1 ) {
                            $is_first_real_payment = true;
                        }
                        elseif( $subscription->trial && $subscription->trial_amount > 0.00 && $subscription->txn_count == 2 ) {
                            $is_first_real_payment = true;
                        }

                    }

                    if ( $is_first_real_payment ) {
                        
                        // Check for exclusion
                        if ($this->core->exclude_user($user_ID)) return;

                        $this->core->add_creds(
                            'product_recurring',
                            $user_ID,
                            $this->prefs['recurring-creds'],
                            $this->prefs['recurring-log'],
                            0,
                            '',
                            $this->mycred_type
                        );

                    }

                }

            }

        }

        //arrange data
        public function  mycred_memberpress_arrange_data( $data ){

            $hook_data = array();
            
            foreach ( $data['product'] as $key => $value ) {
                
                $hook_data[$key]['creds']                         = $data['creds'][$key];
                $hook_data[$key]['log']                           = $data['log'][$key];
                $hook_data[$key]['specific_product_recurring']    = $data['specific_product_recurring'][$key];
                $hook_data[$key]['specific-recurring-creds']      = $data['specific-recurring-creds'][$key];
                $hook_data[$key]['specific-recurring-log']        = $data['specific-recurring-log'][$key];
                $hook_data[$key]['product']                       = $value;
            }
            
            return $hook_data;

        }

        /**
         * Preference for Login Hook
         * @since 0.1
         * @version 1.2
         */
        public function preferences() {

            $prefs = $this->prefs;

            ?>
            <div class="hook-instance">
                <div class="row">
                    <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
                            <input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
                            <input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
                            <span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="<?php echo $this->field_id( 'recuring' ); ?>"><?php _e( 'Check for recurring.', 'mycred' ); ?></label>
                            <input class="mycred-recurring-check" style="margin-top: 8px" type="checkbox" name="<?php echo $this->field_name('product_recurring'); ?>" value="recurring" <?php if ($prefs['product_recurring'] == "recurring") echo "checked='checked'"; ?> id="<?php echo $this->field_id( 'recuring' ) ?>">
                        </div>
                    </div>
                </div>
                 <div class="row">
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 recurring-check recurring-check-creds" style="<?php echo $prefs['product_recurring'] == 'recurring' ? 'display: block' : 'display: none'; ?>">
                            <div class="form-group">
                                <label for="<?php echo $this->field_id( 'recurring-creds' ); ?>"><?php echo $this->core->plural(); ?></label>
                                <input type="text" name="<?php echo $this->field_name( 'recurring-creds' ); ?>" id="<?php echo $this->field_id( 'recurring-creds' ); ?>" value="<?php echo $this->core->number( $prefs['recurring-creds'] ); ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 recurring-check recurring-check-log" style="<?php echo $prefs['product_recurring'] == 'recurring' ? 'display: block' : 'display: none'; ?>">
                            <div class="form-group">
                                <label for="<?php echo $this->field_id( 'recurring-log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
                                <input type="text" name="<?php echo $this->field_name( 'recurring-log' ); ?>" id="<?php echo $this->field_id( 'recurring-log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['recurring-log'] ); ?>" class="form-control" />
                                <span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
                            </div>
                        </div>
                    </div>
            </div><?php 

            if (  count ( $prefs['memberpress_product']['product'] ) > 0 ) {
                
                $hooks = $this->mycred_memberpress_arrange_data(  $prefs['memberpress_product'] );

                $this->mycred_memberpress_specific( $hooks, $this );
            
            }
            else {

                $default = array(
                    array(
                        'creds'          => '10',
                        'log'            => '%plural% for Purchasing Subscription Product.',
                        'product'        => '',
                        'specific_product_recurring' => '',
                        'specific-recurring-creds' => '1',
                        'specific-recurring-log' => '%plural% for Purchasing Subscription Product.',
                    )
                );
                $this->mycred_memberpress_specific( $default, $this );
            }
            ?>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <?php add_filter('mycred_memberpress_hook_limits', array($this, 'custom_limit')); ?>
                            <label for="<?php echo esc_attr( $this->field_id( 'limit' ) ); ?>"><?php _e( 'Limit', 'mycred_memberpress_integration' ); ?></label>
                            <?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
                        </div>
                    </div>
                </div>

            <?php
        }

        /**
         * Sanitize Preferences
         * @since 1.6
         * @version 1.0
         * If the hook has settings, this method must be used
         * to sanitize / parsing of settings.
         */
        public function sanitise_preferences( $data ) {

            $new_data = array();
            
                $new_data['creds'] = ( !empty( $data['creds'] ) ) ? floatval( $data['creds'] ) : 1;
                $new_data['log'] = ( !empty( $data['log'] ) ) ? sanitize_text_field( $data['log'] ) : '%plural% for Purchasing Subscription Product';
                $new_data['mycred_check_addmore'] = ( !empty( $data['mycred_check_addmore'] ) ) ? sanitize_text_field( $data['mycred_check_addmore'] ) : '';
                $new_data['product_recurring'] = ( !empty( $data['product_recurring'] ) ) ? sanitize_text_field( $data['product_recurring'] ) : '';
                $new_data['recurring-log' ] = ( !empty( $data['recurring-log' ] ) ) ? sanitize_text_field( $data['recurring-log' ] ) : '%plural% for Purchasing OneTime or Recurring Product';
                $new_data['recurring-creds' ] = ( !empty( $data['recurring-creds' ] ) ) ? floatval( $data['recurring-creds' ] ) : 1;


                if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
                    $new_data['limit'] = sanitize_text_field( $data['limit'] );
                    $limit = $new_data['limit'];
                    if ( $limit == '' ) $limit = 0;

                    $new_data['limit'] = $limit . '/' . $data['limit_by'];
                    unset( $data['limit_by'] );
                }

                foreach ( $data['memberpress_product'] as $data_key => $data_value ) {

                    foreach ( $data_value as $key => $value) {

                        if ( $data_key == 'creds' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( !empty( $value ) ) ? floatval( $value ) : 10;
                        }
                        else if ( $data_key == 'log' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '%plural% for Purchasing Subscription Product.';
                        }
                        else if ( $data_key == 'product' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '';
                        }
                        else if ( $data_key == 'specific_product_recurring' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( isset( $value ) ) ? sanitize_text_field( $value ) : '0';
                        }
                        else if ( $data_key == 'specific-recurring-creds' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '';
                        }
                        else if ( $data_key == 'specific-recurring-log' ) {
                            $new_data['memberpress_product'][$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '%plural% for Purchasing Subscription Product.';
                        }
                    }
                    
                }
                return $new_data;

            }

        public function mycred_memberpress_field_name( $type, $attr ){

            $hook_prefs_key = 'mycred_pref_hooks';

            if ( $type != MYCRED_DEFAULT_TYPE_KEY ) {
                $hook_prefs_key = 'mycred_pref_hooks_'.$type;
            }

            return "{$hook_prefs_key}[hook_prefs][specific_product][memberpress_product][{$attr}][]";

        }

        public function mycred_memberpress_specific($data,$obj){

            $prefs = $this->prefs;

            global $wpdb;
            $table_name = $wpdb->prefix . 'posts';
            $results = $wpdb->get_results(
                "SELECT $table_name.post_title, $table_name.ID FROM {$table_name} WHERE $table_name.post_type = 'memberpressproduct' AND $table_name.post_status = 'publish'"
            );?>

            <div class="hook-instance checking">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hook-title">
                            <h3><?php _e( 'Specific', 'mycred_memberpress_integration' ); ?></h3>
                        </div>
                        <div>
                            <label class="mycred_memberpress_check" style=" display: block; margin: 14px 0px;">
                            <input type="checkbox" name="<?php echo esc_attr( $this->field_name( 'mycred_check_addmore' ) ); ?>" id="<?php echo esc_attr( $this->field_id( 'mycred_check_addmore' ) ); ?>" value="1" <?php if( $prefs['mycred_check_addmore'] == '1') echo "checked = 'checked'"; ?> />
                            Enable Specific</label>
                        </div>
                    </div>
                </div>
                <?php
                foreach($data as $prefs)
                {
                    ?> 
                    <div class="hook-instance " style="margin-bottom: 20px; ">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="<?php echo $this->field_id( 'Select Product' ); ?>"><?php _e( 'Select Product', 'mycred_memberpress_integration' ); ?></label>
                                    <select class="mycred_memberpress_product" name="<?php echo esc_attr( $this->mycred_memberpress_field_name( $obj->mycred_type,'product') ); ?>" id="<?php echo $this->field_id( 'Select Product' ) ?>">
                                        <option value="">Select Product</option>
                                        <?php
                                        foreach ($results as $row)
                                        {
                                            $post_meta = get_post_meta($row->ID);
                                            $price = $post_meta['_mepr_product_price'][0];
                                            if($price > 0.00)
                                            {
                                                if($row->ID == $prefs['product'])
                                                {
                                                    echo '<option selected="selected" value="'.$row->ID.'">'.$row->post_title.'</option>';
                                                }
                                                else
                                                {
                                                    echo '<option value="'.$row->ID.'">'.$row->post_title.'</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="<?php echo esc_attr( $obj->field_id( 'creds' ) ); ?>"><?php echo esc_html( $obj->core->plural() ); ?></label>
                                    <input type="text" name="<?php echo esc_attr( $this->mycred_memberpress_field_name($obj->mycred_type, 'creds' ) ); ?>" id="<?php echo esc_attr( $obj->field_id( 'creds' ) ); ?>" value="<?php echo esc_attr( $obj->core->number( $prefs['creds'] ) ); ?>" class="form-control mycred-memberpress-creds" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="<?php echo esc_attr( $obj->field_id( 'log' ) ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
                                    <input type="text" name="<?php echo esc_attr( $this->mycred_memberpress_field_name($obj->mycred_type, 'log' ) ); ?>" id="<?php echo esc_attr( $obj->field_id( 'log' ) ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control mycred-memberpress-log" />
                                    <span class="description"><?php echo $obj->available_template_tags( array( 'general' ) ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="<?php echo $this->field_id( 'specific-recuring' ); ?>"><?php _e( 'Check for recurring', 'mycred' ); ?></label>
                                    
                                    <input class="mycred-specific-recurring-check" style="margin-top: 8px" type="checkbox" <?php if ($prefs['specific_product_recurring'] == "1") echo "checked='checked'"; ?> id="<?php echo $this->field_id( 'specific-recuring' ) ?>">
                                    <input class="specific-product-recurring" type="hidden" name="<?php echo esc_attr( $this->mycred_memberpress_field_name( $obj->mycred_type , 'specific_product_recurring' ) ); ?>" value="<?php echo !empty($prefs['specific_product_recurring'] )  ? '1' : '0' ?>">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 specific-recurring-check specific-recurring-check-creds" style="<?php echo $prefs['specific_product_recurring'] == '1' ? 'display: block' : 'display: none'; ?>">
                                <div class="form-group">
                                    <label for="<?php echo $this->field_id( 'specific-recurring-creds' ); ?>"><?php echo $this->core->plural(); ?></label>
                                    <input type="text" name="<?php echo esc_attr( $this->mycred_memberpress_field_name($obj->mycred_type, 'specific-recurring-creds' ) ); ?>" id="<?php echo $this->field_id( 'specific-recurring-creds' ); ?>" value="<?php echo $this->core->number( $prefs['specific-recurring-creds'] ); ?>" class="form-control" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 specific-recurring-check specific-recurring-check-log" style="<?php echo $prefs['specific_product_recurring'] == '1' ? 'display: block' : 'display: none'; ?>">
                                <div class="form-group">
                                    <label for="<?php echo $this->field_id( 'specific-recurring-log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
                                    <input type="text" name="<?php echo esc_attr( $this->mycred_memberpress_field_name($obj->mycred_type, 'specific-recurring-log' ) ); ?>" id="<?php echo $this->field_id( 'specific-recurring-log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['specific-recurring-log'] ); ?>" class="form-control" />
                                    <span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group specific-hook-actions textright mycred-specific-memberpress-button">
                                    <button class="button button-small mycred-add-memberpress-hook" type="button">Add More</button>
                                    <button class="button button-small mycred-remove-memberpress-hook" type="button">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                } ?>
            </div> <?php
        }
    }
endif;
