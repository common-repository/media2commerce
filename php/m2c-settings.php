<?php
/*
* Media2Commerce
* Settings API helper class
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
*  
*/





DEFINE('M2C_MAX_TYPES', 20);


class m2c_settings {


     //constructor
    function m2c_settings() {
        global $m2c_plugin_settings;


        //attempt to load premium file (not used for lite)
        @include_once('premium-settings.php');
        
        //initiliaze variables
        $m2c_plugin_settings  = $this->plugin_initialize_options_array();  
        //add actions
        if (function_exists('add_action')) {

          add_action('admin_init', array( $this, 'plugin_admin_init'));
          add_action('admin_menu', array( $this, 'plugin_admin_add_page'));
        }        
          
    }

     /**
     * enqueue the scripts and styles
     */ 
    function plugin_options_enqueue_scripts() {  ;
        //wp_register_script( 'knockout-js', plugins_url( '/js/knockout-2.3.0.js' , dirname(__FILE__) ) , array('jquery','media-upload','thickbox') );  
        wp_register_script( 'm2c-script', plugins_url( '/js/m2c-script.js' , dirname(__FILE__) ) , array('jquery','media-upload') );  
        wp_register_style( 'm2c-style', plugins_url( '/css/m2c-style.css' , dirname(__FILE__) ) );  
        wp_enqueue_script('jquery');  
        //wp_enqueue_script('thickbox');  
        //wp_enqueue_style('thickbox');  
        wp_enqueue_script('media-upload');  
        //wp_enqueue_script('knockout-js');  
        wp_enqueue_script('m2c-script'); 
        wp_enqueue_style('m2c-style');  

      
          
    } 


     /**
     * return the settings
     */ 
    function plugin_initialize_options_array(){
          
          $group = __( "eightmcPlugin", M2C_PLUGIN_TEXTDOMAIN); // define group
          $page_name = __( "m2c_display", M2C_PLUGIN_TEXTDOMAIN); // eg media/discussion/reading or custom   
          $title = __( "Media2Commerce", M2C_PLUGIN_TEXTDOMAIN);  // admin page title 
          $intro_text = __( "This page controls the settings for Media2Commerce", M2C_PLUGIN_TEXTDOMAIN); // text displayed below title
          $nav_title = __( "Media2Commerce Settings", M2C_PLUGIN_TEXTDOMAIN); // name of page in context menu
  
           
          /*  SECTIONS ARRAY
             * title: the title of the section
             * description: description of the section
             * fields: a array of field items key => array of options
              FIELD ARRAY OPTIONS
              * label: field label.
              * description: the field description displayed adjacent to the field. 
              * suffix: eg px, em, diplayed in italics adjacent to the field
              * default_value: default value of field when empty
              * dropdown: a drop down function, specify the drop down parameter name 
              * function: optional function to render field
              * onchange: option javascript call on dropdown change (currently only for dropdown items)
              * callback: optional function to validate field
              * field_class: the class to be assigned to the field
          */


          $sections = array(
             'section_name_one' => array(
                'title' => "Media2Commerce Settings",
                'description' => __( "Media 2 Commerce Default Settings", M2C_PLUGIN_TEXTDOMAIN),
                'fields' => array( 
                  'm2c_default_types' => array (
                      'label' => "Default Types",
                      'description' => __( "The default types are product variations that are added automatically when you upload an image", M2C_PLUGIN_TEXTDOMAIN),
                      'default_value' => array( array( 'type_name' => "Standard", 'type_price' => "9.99", 'scaling' =>  "20", 'quality' => "100" ) , array('type_name' => "Large", 'type_price' => "29.99", 'scaling' =>  "50", 'quality' => "10"), array( 'type_name' => "Super XL", 'type_price' => "49.99", 'scaling' =>  "100", 'quality' => "100" ) ),
                      'function' => 'plugin_setting_type',
                      'onchange' => '',
                      'callback' => 'type_callback',
                      'field_class' => "",
                      ),
            
                    ),
                  ),
              
              );
          



           //Various Dropdown Options
          $dropdown_options = array (
              'dd_text_colour' => array (
                  '#f00' => __( "Red", M2C_PLUGIN_TEXTDOMAIN) ,
                  '#0f0' => __( "Green", M2C_PLUGIN_TEXTDOMAIN),
                  '#00f' => __( "Blue", M2C_PLUGIN_TEXTDOMAIN),
                  '#fff' => __( "White", M2C_PLUGIN_TEXTDOMAIN),
                  '#000' => __( "Black", M2C_PLUGIN_TEXTDOMAIN),
                  '#aaa' => __( "Gray", M2C_PLUGIN_TEXTDOMAIN),
                  ),
              'dd_background_colour' => array (
                  'none' => __( "Transparent (None)", M2C_PLUGIN_TEXTDOMAIN) ,
                  '#f00' => __( "Red", M2C_PLUGIN_TEXTDOMAIN) ,
                  '#0f0' => __( "Green", M2C_PLUGIN_TEXTDOMAIN),
                  '#00f' => __( "Blue", M2C_PLUGIN_TEXTDOMAIN),
                  '#fff' => __( "White", M2C_PLUGIN_TEXTDOMAIN),
                  '#000' => __( "Black", M2C_PLUGIN_TEXTDOMAIN),
                  '#aaa' => __( "Gray", M2C_PLUGIN_TEXTDOMAIN),
                  ),
              'dd_position' => array (
                  'tl' => __( "Top Left", M2C_PLUGIN_TEXTDOMAIN),
                  'tc' => __( "Top Middle", M2C_PLUGIN_TEXTDOMAIN),
                  'tr' => __( "Top Right", M2C_PLUGIN_TEXTDOMAIN),
                  'cl' => __( "Middle Left", M2C_PLUGIN_TEXTDOMAIN),
                  'cc' => __( "Middle", M2C_PLUGIN_TEXTDOMAIN),
                  'cr' => __( "Middle Right", M2C_PLUGIN_TEXTDOMAIN),
                  'bl' => __( "Bottom Left", M2C_PLUGIN_TEXTDOMAIN),
                  'bc' => __( "Bottom Middle", M2C_PLUGIN_TEXTDOMAIN),
                  'br' => __( "Bottom Right", M2C_PLUGIN_TEXTDOMAIN),
                  'rp' => __( "Tile X and Y", M2C_PLUGIN_TEXTDOMAIN),
                  ),
              'dd_boolean' => array (
                  'true' => __( "Enabled", M2C_PLUGIN_TEXTDOMAIN),
                  'false' => __( "Disabled", M2C_PLUGIN_TEXTDOMAIN),
                  ),
              'dd_onoff' => array (
                  'on' => __( "On", M2C_PLUGIN_TEXTDOMAIN),
                  'off' => __( "Off", M2C_PLUGIN_TEXTDOMAIN),
                  ),
              );


          $vars = array(
                'group' => $group,
                'page_name' => $page_name,
                'title' => $title,
                'intro_text' => $intro_text,
                'nav_title' => $nav_title,
                'sections' => $sections,
                'dropdown_options' => $dropdown_options,
              );


          return $vars;

    }




     
    /**
     * add this page to the Settings tab in the admin panel
     */ 
    function plugin_admin_add_page() {
      global $m2c_plugin_settings, $page_name;
      $page_name = add_options_page($m2c_plugin_settings['title'], $m2c_plugin_settings['nav_title'], 'manage_options', $m2c_plugin_settings['page_name'], array( &$this,'plugin_options_page'));

      // Using registered $page handle to hook script load
      add_action('admin_print_scripts-' . $page_name, array( &$this, 'plugin_options_enqueue_scripts'));
    }
     

    /**
     * load the options page
     */ 
    function plugin_options_page() {
      global $m2c_plugin_settings;
      //var_dump($this->get_types_as_array());
      printf('</pre>
      <div class = "wrap">
      <div id="icon-options-general" class="icon32"><br /></div>
      <h2>%s</h2>
      <p>%s</p>',$m2c_plugin_settings['title'],$m2c_plugin_settings['intro_text']);
      echo "<div class = 'eightmc-box-left' >";
      echo "<form action='options.php' method='post'>";

       settings_fields($m2c_plugin_settings['group']);
       $this->plugin_do_settings_sections($m2c_plugin_settings['page_name']);
       printf('<br/><br/>&nbsp;<input type="submit" name="Submit" class = "submit-btn" value="%s" /></form>
              <pre>
              ',__('Save Changes'));
       echo "</div>  <!-- end eightmc-box-left -->";
       echo "<div class = 'eightmc-box-right'>";
       $this->plugin_side_boxes();
       echo "</div> <!-- end eightmc-box-right -->";
       echo "</div> <!-- end wrap -->";

    }

    /**
    * custom settings section
    */
    function plugin_do_settings_sections( $page ) {
      global $wp_settings_sections, $wp_settings_fields;

      if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
        return;

      foreach ( (array) $wp_settings_sections[$page] as $section ) {
        echo "<div class = 'postbox'>";
        if ( $section['title'] ){
          echo "<h3 class ='section-title'>{$section['title']}</h3>\n";
        }
          
        echo "<div class = 'setting-content'>";
        if ( $section['callback'] )
          call_user_func( $section['callback'], $section );

        if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
          continue;
        echo '<table class="form-table">';
        do_settings_fields( $page, $section['id'] );
        echo '</table>';
        echo "</div> <!-- end setting-content -->"; //end setting-content
        echo "</div> <!-- end postbox -->"; //end postbox
      }
    }

    
    /**
     * add the settings
     */ 
    function plugin_admin_init(){
      global $m2c_plugin_settings;

      foreach ($m2c_plugin_settings['sections'] AS $section_key=>$section_value) :
        add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $m2c_plugin_settings['page_name'], $section_value);
        foreach ($section_value['fields'] AS $field_key=>$field_value) :
          $function = (!empty($field_value['dropdown'])) ? array( &$this, 'plugin_setting_dropdown' ) : array( &$this, 'plugin_setting_string' );
          $function = (!empty($field_value['function'])) ? array( &$this,  $field_value['function'] ) : $function;
          $callback = (!empty($field_value['callback'])) ? array( &$this,  $field_value['callback'] ) : NULL;
          add_settings_field($m2c_plugin_settings['group'].'_'.$field_key, $field_value['label'], $function, $m2c_plugin_settings['page_name'], $section_key,array_merge($field_value,array('name' => $m2c_plugin_settings['group'].'_'.$field_key)));
          register_setting($m2c_plugin_settings['group'], $m2c_plugin_settings['group'].'_'.$field_key,$callback);
          endforeach;
        endforeach;
    }
     
     /**
     * add options to wordpress options API for the Plugin
     * initialize these options
     */
     function m2c_add_options(){      
      global $m2c_plugin_settings;

      foreach ($m2c_plugin_settings['sections'] AS $section_key=>$section_value) :
        foreach ($section_value['fields'] AS $field_key=>$field_value) :
          add_option($m2c_plugin_settings['group'].'_'.$field_key, array("text_string" => $field_value['default_value']));
          endforeach;
        endforeach; 

      //update options for versions etc.
      //update_option('plugin_version', PLUGIN_VERSION);
 
    }

    /**
     * remove options from wordpress options API for the WP Image Protect Premium Plugin
     */
    function wpipp_remove_options(){
      global $m2c_plugin_settings;

      /* delete options if  */
      /*
      foreach ($m2c_plugin_settings["sections"] AS $section_key=>$section_value) :
        foreach ($section_value['fields'] AS $field_key=>$field_value) :
          delete_option($m2c_plugin_settings['group'].'_'.$field_key);
          endforeach;
        endforeach; 
      */

      //delete options  
      //delete_option('plugin_version');

    }

    /**
    * print section text
    */
    function plugin_section_text($value = NULL) {
      global $m2c_plugin_settings;

      printf("%s",$m2c_plugin_settings['sections'][$value['id']]['description']);
    }


    /**
    * Renderer for a type
    */  
    function plugin_setting_type($value = NULL) {
      $options = get_option($value['name']);

 
      //special case for 0 string use asci &#48;
      $counter = 0;
      ?>
      <ul id = 'default-types' >
        <li>
          <span class = "descriptor" id = "type-name-descriptor">Type Name</span>
          <span class = "descriptor" id = "type-price-descriptor">Default Price</span>
          <span class = "descriptor" id = "type-scaling-descriptor">Default Scaling</span>
          <span class = "descriptor" id = "type-quality-descriptor">Default Quality</span>
        </li>
      <?php
      foreach ($options['text_string'] as $index => $type) {
        printf('<li id = "%s" >' , "row-".$counter);
        printf('<input id="%s" type="hidden" name="%1$s" class="%2$s" value="%3$s"  />',
          "typecount-".$counter,
          (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
          $counter);
  
        // [text_string] => Array ( [0] => Array ( [type] => Default [scaling] => 100 [quality] => 100 ) [1] => Array ( [type] => Low Res [scaling] => 100 [quality] => 10 ) ) 
        foreach ($type as $item => $stor) {
          printf('<input id="%s" type="text" name="%1$s[text_string]" class="%4$s %2$s" value="%3$s" />',
            $item."-".$index,
            (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
            (!empty ($stor)) ? $stor : $default_value,
            $item);

        
        }

        printf('<a  name="%s" class="%2$s del-type" id="%1$s" onclick="%3$s">Delete Type</a>',
          "delete-".$counter,
          (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
          "deleteType('" . $counter . "');");
        
        $counter++;
        print "</li>";

      }
      
      ?>
      </ul ><!-- end default-lists -->

      <?php

      printf('<a name="%s" class="%2$s add-type" id="add-type" onclick="%3$s">Add New</a>',
          "add-".$counter+1,
          (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
          "addType();");

      

    }

    /*
    * callback for type
    */
    function type_callback($data){
      global $m2c_plugin_settings;

      $is_content_valid = true;

      $current_value = $this->get_option_from_wp('m2c_default_types');

      $count_types = $this->count_types();
      $new_values = array();
      $name_key = 0;
      for($i = 0; $i < $count_types; $i++){
          while(!array_key_exists('type_name-'.$name_key, $_POST) && $name_key <= M2C_MAX_TYPES){
              $name_key++;
          }

          $new_values[$i] = $this->populate_array($name_key);
          $is_content_valid = $is_content_valid &&  $this->check_type_array($new_values[$i], $i);

          $name_key++;
      }


      $db_values_to_save = array('text_string' =>  $new_values);



      //add_option($m2c_plugin_settings['group'].'_'.$field_key, array("text_string" => $field_value['default_value']));
      if($is_content_valid==true) {
        return $db_values_to_save;
      } else {
        return $current_value;

      }
      

    }

    function populate_array($array_index){
      global $_POST;
      $type_arr = array();
      $type_arr['type_name'] = sanitize_text_field( $_POST['type_name-'.$array_index]['text_string'] );
      $type_arr['type_price'] = sanitize_text_field( $_POST['type_price-'.$array_index]['text_string'] );
      $type_arr['scaling'] = sanitize_text_field( $_POST['scaling-'.$array_index]['text_string'] );
      $type_arr['quality'] = sanitize_text_field( $_POST['quality-'.$array_index]['text_string'] );
      return $type_arr;
    }

    function check_type_array($array_to_check, $index){
      $array_valid = true;

      $type_name = $array_to_check['type_name'];
      //no checking on type_name 

      $type_price = $array_to_check['type_price'];
      if(!is_numeric($type_price)||!preg_match("/^[0-9]+(?:\.[0-9]{1,2})?$/", $type_price)){
        add_settings_error( 'type_price', 'int_not_valid', 'Price for '. $type_name .' must be a numeric valid value');
        $array_valid = false;
      }
      $scaling = $array_to_check['scaling'];
      if(!is_numeric($scaling)||intval($scaling)<0||intval($scaling)>100){
        add_settings_error( 'scaling', 'int_not_valid', 'Scaling for '. $type_name .' must be a value between 0 and 100' );
        $array_valid = false;
      }
      $quality = $array_to_check['quality']; 
      if(!is_numeric($quality)||intval($quality)<0||intval($quality)>100){
        add_settings_error( 'quality', 'int_not_valid', 'Quality for '. $type_name .' must be a value between 0 and 100' );
        $array_valid = false;
      }
      return $array_valid;
 
    }




    /*
    * count the types
    */
    function count_types(){
      global $_POST;
        $no_of_types = 0;
        foreach ($_POST as $key => $value) {
          if(substr($key, 0, 9) == "typecount"){
            $no_of_types++;
          }
        }
        return $no_of_types;
    }

    /*
    * used externally to get types 
    */
    function get_types_as_array(){
      global $m2c_plugin_settings;
      //get the option from wp
       $types = get_option($m2c_plugin_settings['group'].'_'.'m2c_default_types');
       $types_arr = array_key_exists('text_string', $types) ? $types['text_string']: array();
       return $types_arr;
    }
     

    /**
    * Renderer for a standard string option
    */  
    function plugin_setting_string($value = NULL) {
      $options = get_option($value['name']);

      //special case for 0 string use asci &#48;
      $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : "&#48;";
      printf('<input id="%s" type="text" name="%1$s[text_string]" class="%2$s" value="%3$s" size="40" /> %4$s %5$s',
        $value['name'],
        (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
        (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
        (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
        (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
    }
     

     /**
     * Renderer for a dropdown option
     */
    function plugin_setting_dropdown($value = NULL) {
      global $m2c_plugin_settings;
      $options = get_option($value['name']);
      $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
      $onchange = (!empty ($value['onchange'])) ? $value['onchange'] : NULL;
      $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
        $chooseFrom = "";
        $choices = $m2c_plugin_settings['dropdown_options'][$value['dropdown']];
      foreach($choices AS $key=>$option) :
        $chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
          $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
        endforeach;
        printf('
    <select id="%s" name="%1$s[text_string]" class="%2$s" onchange="%3$s" >%4$s</select>
    %5$s',$value['name'], (!empty ($value['field_class'])) ? $value['field_class'] : NULL, $onchange, $chooseFrom,
      (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
    }


    /**
     * Renderer for font dropdown option
     */
    function plugin_font_dropdown($value = NULL) {
      global $m2c_plugin_settings;
      $options = get_option($value['name']);
      $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
      $onchange = (!empty ($value['onchange'])) ? $value['onchange'] : NULL;
      $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
        $chooseFrom = "";
        $choices = $m2c_plugin_settings['font_list'];
      foreach($choices AS $key=>$option) :
        $chooseFrom .= sprintf('<option value="%s" id="%4$s" licence_url="%2$s" %3$s>%4$s</option>',
          $key,  $option['licence_file'], ($current_value == $key ) ? ' selected="selected"' : NULL, $option['font_name']);
        if($current_value == $key ){
          //set current licence file url for selected item
          $licence_file_url = $option['licence_file'];
        }
        endforeach;
        printf('
    <select id="%s" name="%1$s[text_string]" class="%2$s" onchange="%3$s" >%4$s</select>
    %5$s   (<a id="licence_url" href="%6$s" target="_blank">Click to view font licence</a>)',$value['name'], (!empty ($value['field_class'])) ? $value['field_class'] : NULL, $onchange, $chooseFrom,
      (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL, $licence_file_url);
    }



      /**
      * Renderer for a section
      */
      function field_renderer_example() {  
          global $m2c_plugin_settings;

          //set the preview url to that of the rendering proxy
          $preview_url = "http://url";
             ?>  
          <div id="live_matwatermark_preview" style="min-height: 100px;">  
              <img style="max-width:100%;" src="<?php echo esc_url( $preview_url ); ?>" />  
          </div>
          <?php  
      } 

      /**
      * Renderer for a text area
      */
      function plugin_setting_textarea($value = NULL) {
      $options = get_option($value['name']);

      //flush rewrite_rules if settings-updated == true
      $settings_updated = $_GET['settings-updated'];
      if($settings_updated=="true"){
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
      }

      //special case for 0 string use asci &#48;
      $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : "&#48;";
      printf('<textarea id="%s"  name="%1$s[text_string]" class="%2$s" value="%3$s" rows="6" cols="120"> %3$s </textarea><br/> %4$s %5$s',
        $value['name'],
        (!empty ($value['field_class'])) ? $value['field_class'] : NULL,
        (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
        (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
        (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
    }





    /*
    * validation for image transparency -example
    */
    function validate_transparency_value($input){
      global $m2c_plugin_settings;

      //get current value to reset to
      $current_value = $this->get_option_from_wp('watermark_transparency');
      $input_string = (!empty ($input['text_string'])) ? $input['text_string'] : NULL;
      if(is_null($input_string)){
        //input is 0 - as expected
        return $input;
      }elseif(!is_numeric($input_string)){
        add_settings_error( 'watermark_transparency', 'int_not_valid', 'Transparency must be a value between 0 and 100' );
        return $current_value;
      }  elseif(intval($input_string)<0){
        add_settings_error( 'watermark_transparency', 'int_too_small', 'Transparency cannot be less than 0' );
        return $current_value;
      } elseif(intval($input_string)>100){
        add_settings_error( 'watermark_transparency', 'int_too_large', 'Transparency cannot be greater than 100' );
        return $current_value;
      } else {
        return $input;
      }
      
    }




    /*
    * return the current value that option is specified in the wp options table
    */

    function get_option_value_from_wp($option_name){
      global $m2c_plugin_settings;
      
      $real_option_value_array = $this->get_option_from_wp($option_name);
      $real_option_value_text_string  = $real_option_value_array['text_value'];
      return $real_option_value_text_string;
    }

    function get_option_from_wp($option_name){
      global $m2c_plugin_settings;

      //the real option name is prefixed with the group name
      $real_option_name = $m2c_plugin_settings['group'] . "_" . $option_name;
      $real_option_value_array = get_option($real_option_name);
      return $real_option_value_array;
    }

    /**
     * reset an option to the original value
     */
     function plugin_reset_option($option_name){
      
      global $m2c_plugin_settings;
      foreach ($m2c_plugin_settings["sections"] AS $section_key=>$section_value) :
        foreach ($section_value['fields'] AS $field_key=>$field_value) :
          if($field_key==$option_name){
            update_option($m2c_plugin_settings['group'].'_'.$field_key, array("text_string" => $field_value['default_value']));
          }
          
          endforeach;
        endforeach; 
        
    }


    /**
    * displays the side boxes 
    */
    function plugin_side_boxes(){
        $this->plugin_support_box();
        $this->plugin_info_box();
    }

    /**
    * display support side box
    */

    function plugin_support_box(){
      ?>
        <div class = 'postbox'>
          <h3 class ='section-title'>Support</h3>          
          <div class = 'setting-content'>
            Need help with the plugin?<br/><br/>
            Try the following:<br/>
            <ul>
              <li><a href = '<?php echo M2C_PLUGIN_WEBSITE; ?>' target = '_blank'>Read the documentation</a></li>
              <li><a href = 'http://wordpress.org/support/plugin/media2commerce' target = '_blank'>Check the support forums</a></li>
              <li><a href = '<?php echo M2C_PLUGIN_WEBSITE; ?>' target = '_blank'>Visit the website</a></li>
            </ul>
          </div> 
        </div> 
      <?php
    }

    /**
    * display support side box
    */
    function plugin_info_box(){
      ?>
        <div class = 'postbox'>
          <h3 class ='section-title'>Unleash the power of this plugin</h3>          
          <div class = 'setting-content'>
            Coming soon... more features and functionality<br/><br/>
            Keep an eye on the Media2Commerce Website for:<br/>
            <ul>
              <li><span class = 'green-text'>&#10004; </span> Apply watermarks to images</li>
              <li><span class = 'green-text'>&#10004; </span> Link images directly to shop items</li>
              <li><span class = 'green-text'>&#10004; </span> Advanced metrics</li>
            </ul>
            <a href = '<?php echo M2C_PLUGIN_WEBSITE; ?>' target = '_blank'>Find Out More</a>
          </div> 
        </div> 
      <?php
    }

    


 
//end class
}
 
$m2c_settings_init = new m2c_settings();
?>