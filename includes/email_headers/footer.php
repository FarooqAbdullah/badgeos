 <?php
  global $achievement_title, $achievement_id;
  $admin_email   = get_bloginfo('admin_email');
  $website_title = get_bloginfo('name');
  $plugin_slug = badgeos_get_directory_url();
?>
                   <br>
                  <br>
                  <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                    <tr style="background-color:#c7eaf2" valign="top">
                      <td align="center">
                        <table cellspacing="0" cellpadding="0" border="0">
                          <tr valign="top" height="18">
                            <td colspan="3"></td>
                          </tr>
                          <tr valign="top">
                            <td colspan="3"><h2 style="font:bold 18px/1 Arial,sans-serif;color:#3b3c3d!important;margin:0"> <span style="color:#3b3c3d">Here’s what <?php echo $website_title;?> said:</span> </h2></td>
                          </tr>
                          <tr valign="top" height="18">
                            <td colspan="3"></td>
                          </tr>
                          <tr valign="top">
                            <td width="55"><?php echo get_avatar( $uid, 55 ); ?> </td>
                            <td width="34"><img src="<?php echo  $plugin_slug.'images/email_quote_icon.png'; ?>" style="display:block" class="CToWUd" width="34" height="40" border="0"></td>
                            <td style="background-color:#fff" width="370">
                              <table cellspacing="0" cellpadding="0" border="0">
                                <tr width="370" valign="top" height="18">
                                  <td colspan="3"></td>
                                </tr>
                                <tr width="370" valign="top" height="18">
                                  <td width="12"></td>
                                  <td width="346"><p style="font:normal 16px/120% Arial,sans-serif;color:#3b3c3d;margin:0"> <?php echo $cong_text; ?> </p></td>
                                  <td width="12"></td>
                                </tr>
                                <tr width="370" valign="top" height="18">
                                  <td colspan="3"></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr valign="top" height="18">
                            <td colspan="3"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr valign="top" height="30">
                      <td colspan="3"></td>
                    </tr>
                    <tr valign="top">
                      <td colspan="5">
                        <table cellspacing="0" cellpadding="0" border="0" width="100%">
                          <tr valign="top">
                            <td>
                              <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr valign="top">
                                <td width="50%">
                                  <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                  
                                    <tr valign="top">
                                      <td colspan="2"><h2 style="font:bold 18px/1 Arial,sans-serif;color:#3b3c3d!important;margin:0"> Credit Details </h2></td>
                                    </tr>
                                    <tr valign="top">
                                      <td colspan="2"><h3 style="font:bold 12px/1 Arial,sans-serif;color:#aaaaaa!important;margin:1em 0 .25em;letter-spacing:0.1em"> Title</h3></td>
                                    </tr>
                                    <tr valign="top">
                                      <td colspan="2"><p style="font:normal 12px/120% Arial,sans-serif;color:#737373;margin:0 0 0 10px;letter-spacing:0.1em"> <?php echo $ach_title ;?></p></td>
                                    </tr>
                                    
                                    <tr valign="top">
                                      <td colspan="2"><h3 style="font:bold 12px/1 Arial,sans-serif;color:#aaaaaa!important;margin:1em 0 .25em;letter-spacing:0.1em"> Issue Date</h3></td>
                                    </tr>
                                    <tr valign="top">
                                      <td colspan="2"><p style="font:normal 12px/120% Arial,sans-serif;color:#737373;margin:0 0 0 10px;letter-spacing:0.1em"> <?php echo date( $date_format, strtotime( $issue_date ) );?></p></td>
                                    </tr>
                                    
                                    
                                    <tr valign="top">
                                      <td colspan="2"><h3 style="font:bold 12px/1 Arial,sans-serif;color:#aaaaaa!important;margin:1em 0 .25em;letter-spacing:0.1em"> Evidence</h3></td>
                                    </tr>
                                    <tr valign="center">
                                      <td><img src="<?php echo $plugin_slug.'images/evidence.png'; ?>" style="display:block;margin:0 0 0 10px" class="CToWUd"></td>
                                      <td><p style="font:normal 12px/20px Arial,sans-serif;margin:0"> <a href="<?php echo $badgeos_evidence_url;?>" style="color:#2499bb;text-decoration:none;font-weight:bold" target="_blank">View Evidence</a> </p></td>
                                    </tr>
                                  </table>
                                </td>
                                <td width="50%">
                                  <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                     <tr valign="top">
                                      <td><h2 style="font:bold 18px/1 Arial,sans-serif;color:#3b3c3d!important;margin:0"> Issuer Details </h2></td>
                                    </tr>
                                      <tr valign="top">
                                      <td><h3 style="font:bold 12px/1 Arial,sans-serif;color:#aaaaaa!important;margin:1em 0 .25em;letter-spacing:0.1em"> Issuer</h3></td>
                                    </tr>
                                    <tr valign="center">
                                      <td><p style="font:normal 12px/21px Arial,sans-serif;margin:0 0 0 10px"> <a href="<?php echo site_url(); ?>" style="color:#2499bb;text-decoration:none;font-weight:bold" target="_blank"><?php echo $website_title;?></a> </p></td>
                                    </tr>
                                  </table>
                                </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr valign="top">
                            <td colspan="3"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr width="545" style="background-color:#f0f0f0" valign="top" height="66">
                      <td colspan="5">
                        <table cellspacing="0" cellpadding="0" border="0">
                          <tr width="545" valign="top" height="30">
                            <td colspan="5"></td>
                          </tr>
                          <tr width="545" valign="top">
                            <td width="36"></td>
                            <td width="223" valign="middle">
                              <?php if ( has_custom_logo() ) : ?>
                                <div class="site-logo">
                                  <?php 
                                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                                    $custom_logo_url = wp_get_attachment_image_url( $custom_logo_id , 'full' );
                                    echo '<img src="' . esc_url( $custom_logo_url ) . '" width="100px" alt="">';
                                  ?>
                                </div>
                              <?php endif; ?>      
                            </td>
                            <td width="25"></td>
                            <td width="223">
                              <p style="font:normal 11px/120% Arial,sans-serif;color:#737373;margin:0.3em 0"> This email was sent to <a style="color:#2499bb;text-decoration:none;font-weight:bold" href="mailto:<?php echo $to_email;?>" target="_blank"><?php echo $to_email;?></a>.</p>
                              <p style="font:normal 11px/120% Arial,sans-serif;color:#737373;margin:0.3em 0">This email was sent by <a style="color:#737373;text-decoration:none;font-weight:bold" href="mailto:<?php echo $admin_email;?>" target="_blank"><?php echo $admin_email;?></a> via <?php echo $website_title;?>. Reply to opt out of similar messages from this sender.</p>
                              <span class="HOEnZb"><font color="#888888"></font></span>
                            </td>
                            <td width="36"></td>
                          </tr>
                          <tr width="545" valign="top" height="30">
                            <td colspan="5"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

            <!-- END MAIN CONTENT AREA -->
            </table>

            <!-- START FOOTER -->
            <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
              
            </div>
            <!-- END FOOTER -->

          <!-- END CENTERED WHITE CONTAINER -->
          </div>
        </td>
        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
      </tr>
    </table>
  </body>
</html>