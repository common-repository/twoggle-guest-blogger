<?php
/**
 * Plugin Name: Twoggle Guest Blogger
 * Plugin URI: http://twoggle.com/wordpress-plugins/twoggle-guest-blogger
 * Description: Easily get new, unique content from guest bloggers without giving out wp-admin access.
 * Version: 1.0
 * Author: gvanto
 * Author URI: http://twoggle.com
 * Requires at least: 3.0
 * Tested Up to: 3.9.1
 * License: GPL v2
 */
$twgb = new Twoggle_Guest_Blogger();

class Twoggle_Guest_Blogger {

   private $category;
   private $author;
   public $thanks_page;
   
   private $max_no_tags = 4;
   
   function __construct() 
   {
      // User can insert this shortcode on their guest-blogging page
      add_shortcode('twgb', array($this, 'twgb_guestblogform_shortcode'));
            
      // Register both logged in and non-loggedin users' guest save callbacks
      add_action('wp_ajax_twgb_submit_and_save_guests_post', array($this, 'twgb_submit_and_save_guests_post_ajax_callback'));
      add_action('wp_ajax_nopriv_twgb_submit_and_save_guests_post', array($this, 'twgb_submit_and_save_guests_post_ajax_callback'));
      
      // Option menu & settings page callbacks
      add_action('admin_menu', array($this, 'twgb_register_option_page'));
      add_action('admin_init', array($this, 'twgb_settings_page_content'));
      
      // Add JS file
      add_action('init', array($this, 'twgb_js_init'));
   }
     
   function twgb_js_init() {      
       wp_enqueue_script( 'twgb-js', plugins_url( '/js/twoggle-guest-blogger.js', __FILE__ ), array( 'jquery'));
       wp_enqueue_style('twgb-css', plugins_url( '/css/twoggle-guest-blogger.css', __FILE__ ));
   }
   
   // Note: use return shortcode contents for it to appear in correct place
   function twgb_guestblogform_shortcode($atts) 
   {
      extract(shortcode_atts(array(
                 'cat' => '',
                 'author' => '',
                 'thanks' => '',
                      ), $atts)
              );
      
      $this->category = $cat;
      $this->author = $author;
      $this->thanks_page = $thanks;
      
      ob_start();
      ?>

      <form class="twoggle-guest-blogger" id="twgb-form" onsubmit="return false" method="post">
         
         <div class="info" style="display: none"></div>
                  
         <!-- Title -->
         <div class="twgb-field">
            <div class="title">Post Title</div>            
            <div class="data">
               <input type="text" id="twgb-post-title" name="gb_title" size="60" required="required" placeholder="<?php echo 'Post title'; ?>">  
            </div>                      
         </div>
         
         <!-- Content -->
         <div class="twgb-field">
            <div class="title">Post content</div>
            
            <div class="twgb-content-toolbar">
               <input id="btn-heading" type="submit" value="Sel » Heading">
               <input id="btn-link" type="submit" value="Sel » Link">
               <input id="btn-bold" type="submit" value="Sel » Bold">
               <input id="btn-italic" type="submit" value="Sel » Italic">
            </div>
            
            <div class="data">
               <?php wp_nonce_field('twgb_submit_and_save_guests_post', 'twgb_submit_and_save_guests_post_unique_key') ?>         
               <textarea id="twgb-post-content" rows="15" cols="72" required="required" name="gb_content" placeholder="Post content"></textarea>
            </div>
            
            <div id="content-info">&nbsp;</div>
         </div>
         
         
         <!-- Image : Use an image host to include <img> -->
         <!-- Not used in form post, just to insert img url into post -->
         <div class="twgb-field">            
            <div class="title">Image URL</div>
            
            <div class="data">
               <input type="text" id="twgb-img-url" name="gb_image" size="60" placeholder="Image URL [direct link to image]">
               
               <div id="img-info">&nbsp;</div>
               
               <div class="post_tag_title">
                  You can upload & host image at (free): <a rel="nofollow" target="_blank" href="http://postimage.org">postimage.org</a>. 
                  <br>Only use <strong>direct</strong> image link, e.g.: http://s27.postimg.org/fijmai6jn/honey_badger.jpg
                  <br>If the image is valid, it will appear below ...
                  
                  <!-- Insert it! http://jsfiddle.net/Znarkus/Z99mK/ -->                  
                  <br><input type="checkbox" id="twgb-chkimg" value="false">
                  <div id="twgb-chkimg-text"> Check me, then click somewhere in the post content to insert image</div>
                         
                  <br>
<!--                  onerror="this.src = '/images/default.png'-->
                  <img id="twgb-img" src="" onerror="onImgLoadError();">
                                    
               </div>
            </div>
         </div>
         
         
         <!-- Tags -->
         <div class="twgb-field">
            <div class="title">Tags</div>
            <div class="data">
               <input type="text" name="gb_tags" size="60" placeholder="Comma Separated Tags">
               <br>
               <?php
               $tags = get_tags(array('hide_empty' => 0));
               $html = '<div class="post_tags" id="post_tags">';
               $html .= '<div class="post_tag_title">Comma separated, maximum 4. Click to add existing:</div>';
               $tag_arr = array();
               foreach ( $tags as $tag ) {
                  //$tag_link = get_tag_link( $tag->term_id );
                  $tag_arr[] = "<a href=\"#\" title=\"Click to add {$tag->name} Tag\" class=\"{$tag->slug}\">{$tag->name}</a>";
               }
               $html .= implode(', ', $tag_arr);
               $html .= '</div>';
               echo $html;
               ?> 
            </div>
         </div>
         
         
         <!-- About Guest Blogger (You) -->
         
         <div class="twgb-field about-you">
            <div class="title">Your name</div>
            <div class="data">
               <input type="text" name="gb_name" size="60" required="required" placeholder="<?php echo 'Your name'; ?>">
            </div>
            
            <div class="title">Your email</div>
            <div class="data">
               <input type="email" name="gb_email" name="gb_email" size="60" required="required" placeholder="<?php echo 'Your email'; ?>">
            </div>
            
            <div class="title">Your website</div>
            <div class="data">
               <input type="text" name="gb_site" size="60" placeholder="<?php echo 'Your website'; ?>">
            </div>
            
            <div class="title">Short guest blogger bio</div>
            <div class="data">
               <textarea id="gb_bio" rows="4" cols="72" required="required" name="gb_bio">{name} is a {insert_profession} at {insert_company|interesting_place}. When he/she is not {insert_awesome_hobby}-ing he/she can be found writing groovy blog posts for {website}.</textarea>
            </div>                     
         </div>
         
         <div class="twgb-field human-verification">
            <div class="title">Human verification</div>
            <div class="data">
               <input type="text" style="width: 60px;" required="required" name="humanverify"> + 3 = 5</div>
         </div>
         
         <!-- hiddens -->
         <input type="hidden" value="<?php echo $this->category; ?>" name="category">         
         <!-- If user is logged in, they will be assigned the author id (although then they can then just
         submit it via wp-admin ... -->
         <input type="hidden" value="<?php echo $this->author; ?>" name="authorid">
         <input type="hidden" value="<?php echo $this->thanks_page; ?>" name="thanks">
         <input type="hidden" value="<?php echo admin_url("admin-ajax.php"); ?>" id="admin_ajax_url">
         
         <div class="twgb-field">            
            <div class="data">
               <input type="submit" value="<?php echo 'Submit Guest Post'; ?>"> <input type="reset" value="<?php echo 'Reset'; ?>">
            </div>
         </div>
         
         <?php /*** By Twoggle Link - Only if user opted in! ***/ ?>
         <?php if (1 == ((int)get_option('twgb_show_by_twoggle')) ) : ?>
         <div class="by-twoggle-row alignright">            
             <a href="http://twoggle.com/wordpress-plugins/twoggle-guest-blogger" target="_blank" style="text-decoration: none;">
                 <img title="Twoggle Guest Blogger WordPress Plugin" alt="Twoggle Guest Blogger WordPress Plugin" src="<?php echo plugins_url( '/img/twoggle-guest-blogger.png', __FILE__ ); ?>" >
             </a>
         </div>         
         <?php endif; ?>
         
      </form>
      
      <?php
      
      $ret = ob_get_contents();
      ob_end_clean();
      
      return $ret;
   }

   function twgb_submit_and_save_guests_post_ajax_callback() 
   {
      header('Content-Type: application/json');
      $gb_title = $_POST["gb_title"];
      $gb_content = $_POST["gb_content"];
      $gb_tags = $_POST["gb_tags"];
      
      $gb_author = $_POST["gb_name"]; // guest blogger
      $gb_email = $_POST["gb_email"];
      $gb_site = $_POST["gb_site"]; 
      $gb_bio = $_POST["gb_bio"]; 
           
      if (empty($_POST['humanverify']) || $_POST['humanverify'] != 2) {
         die(json_encode(array('dead' => 'Human verification failed.')));
      }
      
      //Append BIO field where guest blogger can write something about themselves, including their name, 
      $gb_content .= "\n <br>" . $gb_bio;
            
      // Set Author (User id who will publish post)
      $authorid = (empty($_POST["authorid"])) ? get_option('twgb_author_id') : $_POST["authorid"];
      if (!$authorid) {
         $authorid = 1;
      }
      
      // Category
      $category = get_option('twgb_post_category');
      if (!$category) {
         $category = array(1);
      }         
      if (!is_array($category)) {
         $category = array($category);
      }
      
      // Limit tags:
      $tags = explode(',', $gb_tags);      
      if (count($tags) > 4) {
          $tags_new = array();
          for($i = 0; $i < $this->max_no_tags; $i++) {
            $tags_new[] = trim($tags[$i]);
          }
          $gb_tags = implode(',', $tags_new);
      }      
      
      // Thank you page
      $redirect_to = (empty($_POST["thanks"])) ? get_option('twgb_redirection_url') : $_POST["thanks"];
      if (!$redirect_to) {
         $redirect_to = home_url();
      }
      
      $nonce = $_POST["twgb_submit_and_save_guests_post_unique_key"];

      // Verify the form fields
      if (empty($gb_title) || empty($gb_content) || empty($gb_author) || empty($gb_email) || empty($nonce))
         die(json_encode(array('dead' => 'Please fill the form.')));
      if (!wp_verify_nonce($nonce, 'twgb_submit_and_save_guests_post'))
         die(json_encode(array('dead' => 'Failed Security check')));

      // Post Properties
      $new_post = array(
         'post_title' => $gb_title,
         'post_content' => $gb_content,
         'post_category' => $category, // Usable for custom taxonomies too
         'tags_input' => $gb_tags,
         'post_status' => 'pending', // Choose: publish, preview, future, draft, etc.
         'post_type' => 'post', //'post',page' or use a custom post type if you want to
         'post_author' => $authorid //Author ID
      );
      
      // var_dump($new_post);
      // save the new post
      $pid = wp_insert_post($new_post);
      
      // Notify peeps.
      if ($pid > 0) {
          $notify_ids = get_option('twgb_notify_ids', false);
          
          if ($notify_ids != false && ((int)$notify_ids) != -1) { //-1 == notify nobody              
              
              if (!is_array($notify_ids)) {
                  $notify_ids = array($notify_ids);
              }
              
              $subj =  get_bloginfo( 'name' ) . ": New guest blog submitted by $gb_author <$gb_email>: " . $gb_title;
              $content = "A new guest blog was submitted by $gb_author <$gb_email>: \r\n \r\n";
              $content .= "Post Title: $gb_title \r\n";
              $content .= "Tags: $gb_tags \r\n";
              // Categories
              $cat_names = array();
              foreach ($category as $cat_id) {
                  $cat_names[] = get_cat_name($cat_id);
              }
              $content .= "Category: " . implode(', ', $cat_names) . " \r\n";
              // Author
              $author = get_user_by('id', $authorid);
              $content .= "Author assigned: {$author->user_nicename} <{$author->user_email}> \r\n";
              $content .= "\r\n";              
              $content .= "Guest blogger: $gb_author <$gb_email> \r\n";              
              $content .= "Site: $gb_site \r\n";              
              $content .= "Bio (appended to bottom of post content):\r\n $gb_bio \r\n"; 
              $content .= "\r\n";
              // Post Edit URL
              $p_url = get_admin_url() . "post.php?post=$pid&action=edit";
              $content .= "Review post: $p_url \r\n";
              
              foreach ($notify_ids as $nid) {
                  $u = get_user_by('id', $nid);                  
                  wp_mail($u->user_email, $subj, $content);
              }              
          }          
      }      

      if ($pid) {
         die(json_encode(array(
                    'success' => 'Your post has been submitted successfully. Now redirecting...',
                    'redirect_to' => $redirect_to
                 )));
      }
      die(json_encode(array('dead' => 'Something went wrong')));
   }
   
   
   /*********************************************/
   /*** WP-ADMIN SETTINGS PAGE ******************/ 

   // Called through action in constructor
   function twgb_register_option_page() 
   {
      add_options_page(
              'Twoggle Guest Blogs', 'Twoggle Guest Blogger', 'manage_options', 'twoggle-guest-blogger-settings', array($this, 'twgb_option_page_callback')
      );
   }

   function twgb_option_page_callback() {
      ?>
      
      <style> 
       <!--
       
       .help-text {
          font-size: 0.75em;
          font-weight: normal;
          font-style: italic;
       }
       
      -->
      </style>
      
      <?php
      echo '<div id="icon-options-general" class="icon32"></div><h2>Twoggle Guest Blogger Settings</h2>';
      echo '<div class="wrap">';
      echo '<form method="post" action="options.php">';
      settings_fields('twgb_settings_section');
      do_settings_sections('tw-gb-settings');
      submit_button();
      echo '</form>';
      echo '</div>';
   }

   // Called through action in constructor
   function twgb_settings_page_content() 
   {

      add_settings_section(
              'twgb_settings_section', 'Guest Blogger Settings', array($this, 'twgb_settings_section_callback'), 'tw-gb-settings'
      );
     
      /*** AUTHOR ASSIGNMENT ***/    
      $auth_desc = 'Author to assign guest blogs to';
      $auth_desc .= '<br><span class="help-text">Typically the person who will be managing guest blogs.</span>';
      add_settings_field(
              'twgb_author_id', $auth_desc, array($this, 'twgb_author_id_callback'), 'tw-gb-settings', 'twgb_settings_section'
      );
      register_setting(
              'twgb_settings_section', 'twgb_author_id'
      );
      
      /*** ADDITIONAL NOTIFIES ***/
      $notify_desc = 'Users to notify of a new guest blog post';
      $notify_desc .= '<br><span class="help-text">Hold down the Ctrl (windows) / Command (Mac) button to select multiple users.</span>';
      add_settings_field(
              'twgb_notify_ids', $notify_desc, array($this, 'twgb_notify_ids_callback'), 'tw-gb-settings', 'twgb_settings_section'
      );
      register_setting(
              'twgb_settings_section', 'twgb_notify_ids' , array($this, 'twgb_notify_ids_validate')
      );
      
      
      /*** CATEGORY ASSIGNMENT ***/
      // edit-tags.php?taxonomy=category
      $cat_desc = 'Select guest blog categories';
      $cat_desc .= '<br><span class="help-text">Tip: Create a descriptive category (for example \'Guest_Blog\') ' . '<a href="edit-tags.php?taxonomy=category" target="_blank">here</a>.';
      $cat_desc .=  ' Leaving blank will put guest blog in \'Uncategorized\'.</span>';
      add_settings_field(
              'twgb_post_category', $cat_desc, array($this, 'twgb_post_category_callback'), 'tw-gb-settings', 'twgb_settings_section'
      );
      register_setting(
              'twgb_settings_section', 'twgb_post_category'
      );
            
      
      /*** THANK YOU PAGE REDIRECTION ***/
      $redir_desc = '<br><span class="help-text">Tip: Create a page <a target="_blank" href="edit.php?post_type=page">here</a>.';
      $redir_desc .= 'Thank your guest blogger with a message on a thank you page, eg: ' . get_home_url() . '/thank-you.</span>';      
      //$redir_desc .= 'Leaving blank = homepage (but why not use a nice thank you page? :)</span>';
      add_settings_field(
              'twgb_redirection_url', 'Redirection page after submission (thank you page)' . $redir_desc, array($this, 'twgb_redirection_url_callback'), 'tw-gb-settings', 'twgb_settings_section'
      );
      register_setting(
              'twgb_settings_section', 'twgb_redirection_url'
      );
      
      
      /*** BY TWOGGLE LINK ***/
      $by_twoggle_desc = '<br><span class="help-text">';
      $by_twoggle_desc .= 'We\'d appreciate if you could keep this checked - A very small link (back to the plugin page) will be included at bottom of form.</span>';      
      //$redir_desc .= 'Leaving blank = homepage (but why not use a nice thank you page? :)</span>';            
      add_settings_field(
              'twgb_show_by_twoggle', 'Include small \'by twoggle\' link:' . $by_twoggle_desc, array($this, 'twgb_show_by_twoggle_callback'), 'tw-gb-settings', 'twgb_settings_section'
      );
      register_setting(
              'twgb_settings_section', 'twgb_show_by_twoggle'
      );
   }

   /** Callbacks */
   function twgb_settings_section_callback() {
      echo '<strong>';
      echo 'Please note: Before proceeding ensure you have created a page and included the [twgb] shortcode somewhere on it ';
      echo '(see the <a target="_blank" href="http://twoggle.com/wordpress-plugins/twoggle-guest-blogger">installation guide here</a>).';
      echo '</strong>';
   }

   function twgb_author_id_callback() 
   {
      $users = get_users('orderby=username');      
      $curr_auth = get_option('twgb_author_id');
      
      echo '<select name="twgb_author_id">';
      
      foreach ($users as $u) {
          if (user_can($u, 'publish_posts') ) {
              $selected = ($curr_auth == $u->ID) ? 'selected' : '';
              echo "<option value=\"$u->ID\" $selected>{$u->user_login} ({$u->user_email})</option>";
          }        
      }
      echo '</select>';      
   }
   
   // Process value before getting saved
   function twgb_notify_ids_validate($input) {      
      if ($input == null) {
         $input = -1;
      }      
      return $input;
   }
   
   function twgb_notify_ids_callback() 
   {
      $users = get_users('orderby=username');      
      
      $selected_ids = array();
      
      $notify_ids = get_option('twgb_notify_ids'); //array of id's previously saved
      //print_r($notify_ids);      
      if ($notify_ids != false) {         
         $selected_ids = (is_array($notify_ids)) ? $notify_ids : array($notify_ids);         
      } 
      // Id's have been previously saved, but none selected
      elseif ((int)$notify_ids == -1) {
         $selected_ids = array(); //empty array
      }
      // No id's set, assign to author:
      else {
         // Get Author      
         $curr_auth = get_option('twgb_author_id');
         if ($curr_auth != false) {
            $selected_ids = array($curr_auth);
         }
      }
      
      //print_r($selected_ids);
      //print_r($_POST['twgb_notify_ids']);

//      //todo js: on author_id select, assign that user in this list
      echo '<select name="twgb_notify_ids[]" multiple>';      
      
      foreach ($users as $u) {
        $selected = in_array($u->ID, $selected_ids) ? 'selected' : '';
        echo "<option value=\"$u->ID\" $selected>{$u->user_login} ({$u->user_email})</option>";
      }
      echo '</select>';      
   }
   
   function twgb_post_category_callback() 
   {
      $categories = get_categories(array('type' => 'post', 'hide_empty' => 0));
      $selected_category = get_option('twgb_post_category');
      if (!is_array($selected_category))
         $selected_category = array($selected_category);
      ?>
      <style type="text/css" scoped>
         .selectcategories {
            height: 200px;
            width: 30%;
            min-width: 200px;
            padding-left: 8px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-right: 0;
            background-color: #FFF;
            padding-top: 5px;
         }

      </style>
      <?php
      echo '<div class="selectcategories">';
      foreach ($categories as $category) {
         $html = '<input type="checkbox" name="twgb_post_category[]" value="' . $category->cat_ID . '" ';
         if (in_array($category->cat_ID, $selected_category))
            $html .= 'checked="checked"';
         $html .= ' />' . $category->name . '<br />';
         echo $html;
      }
      echo '</div>';
   }

   function twgb_redirection_url_callback() 
   {
      $url_opt = get_option('twgb_redirection_url');
      //_log("TW: url_opt=$url_opt");
      echo '<input type="text" required name="twgb_redirection_url" value="' . $url_opt . '" class="regular-text" />';
   }
   
   function twgb_show_by_twoggle_callback() 
   {      
      //_log("TW: url_opt=$url_opt");
      $chk =  (1 == ((int)get_option('twgb_show_by_twoggle')) ) ? 'checked' : '';
      echo '<input type="checkbox" name="twgb_show_by_twoggle" ' . $chk . ' value="1" />';            
   }   
}
