<?php
namespace app\Core\Utils;

use app\Core\Application;
use app\Core\DbModel;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends DbModel
{
    public function sendMail($mailTo, $fname, $subject, $msg)
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        //Set SMTP host name                          
        $mail->Host = "smtp.gmail.com";
        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = true;
        //Provide username and password     
        $mail->Username = "";
        $mail->Password = "";
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";
        //Set TCP port to connect to
        $mail->Port = 587;

        $mail->From = "no-reply@myblogpay.com";
        $mail->FromName = "";

        $mail->addAddress($mailTo, ""); //Recipient name is optional 
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = '<!DOCTYPE html>

        <html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
        
        <head>
            <title></title>
            <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
            <meta content="width=device-width, initial-scale=1.0" name="viewport" />
            <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
            <style>
                * {
                    box-sizing: border-box;
                }
        
                body {
                    margin: 0;
                    padding: 0;
                }
        
                a[x-apple-data-detectors] {
                    color: inherit !important;
                    text-decoration: inherit !important;
                }
        
                #MessageViewBody a {
                    color: inherit;
                    text-decoration: none;
                }
        
                p {
                    line-height: inherit
                }
        
                .desktop_hide,
                .desktop_hide table {
                    mso-hide: all;
                    display: none;
                    max-height: 0px;
                    overflow: hidden;
                }
        
                .image_block img+div {
                    display: none;
                }
        
                @media (max-width:500px) {
        
                    .desktop_hide table.icons-inner,
                    .social_block.desktop_hide .social-table {
                        display: inline-block !important;
                    }
        
                    .icons-inner {
                        text-align: center;
                    }
        
                    .icons-inner td {
                        margin: 0 auto;
                    }
        
                    .mobile_hide {
                        display: none;
                    }
        
                    .row-content {
                        width: 100% !important;
                    }
        
                    .stack .column {
                        width: 100%;
                        display: block;
                    }
        
                    .mobile_hide {
                        min-height: 0;
                        max-height: 0;
                        max-width: 0;
                        overflow: hidden;
                        font-size: 0px;
                    }
        
                    .desktop_hide,
                    .desktop_hide table {
                        display: table !important;
                        max-height: none !important;
                    }
        
                    .row-1 .column-1 .block-2.paragraph_block td.pad>div {
                        font-size: 12px !important;
                    }
                }
            </style>
        </head>
        
        <body style="background-color: #fff; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">
<table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation"
style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff;" width="100%">
<tbody>
<tr>
<td>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-1"
        role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="left" border="0" cellpadding="0" cellspacing="0"
                        class="row-content stack" role="presentation"
                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000; width: 480px;"
                        width="480">
                        <tbody>
                            <tr>
                                <td class="column column-1"
                                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;"
                                    width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        class="image_block block-1" role="presentation"
                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                                        width="100%">
                                        <tr>
                                            <td class="pad"
                                                style="width:100%;padding-right:0px;padding-left:0px;">
                                                <div align="center" class="alignment"
                                                    style="line-height:10px"><img
                                                        src="https://myblogpay.com/public/img/1_Untitled-2.jpg"
                                                        style="display: block; height: auto; border: 0; max-width: 96px; width: 100%;"
                                                        width="96" /></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="10" cellspacing="0"
                                        class="paragraph_block block-2" role="presentation"
                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                        width="100%">
                                        <tr>
                                            <td class="pad">
                                                <div
                                                    style="color:#000000;direction:ltr;font-family:Arial, "Helvetica Neue", Helvetica, sans-serif;font-size:14px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:left;mso-line-height-alt:16.8px;">
                                                    <p style="margin: 0; margin-bottom: 16px;">Hey
                                                        ' . $fname . ', ' . $msg . '<br /><br />Warm
                                                        regards,
                                                    </p>
                                                    <p style="margin: 0;"><br />Blogpay Monetization</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="10" cellspacing="0"
                                        class="divider_block block-3" role="presentation"
                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                                        width="100%">
                                        <tr>
                                            <td class="pad">
                                                <div align="center" class="alignment">
                                                    <table border="0" cellpadding="0" cellspacing="0"
                                                        role="presentation"
                                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                                                        width="100%">
                                                        <tr>
                                                            <td class="divider_inner"
                                                                style="font-size: 1px; line-height: 1px; border-top: 1px solid #BBBBBB;">
                                                                <span> </span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="10" cellspacing="0"
                                        class="paragraph_block block-4" role="presentation"
                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                        width="100%">
                                        <tr>
                                            <td class="pad">
                                                <div
                                                    style="color:#8d8c8c;direction:ltr;font-family:Arial, "Helvetica Neue", Helvetica, sans-serif;font-size:12px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:14.399999999999999px;">
                                                    <p style="margin: 0; margin-bottom: 5px;">This email
                                                        was sent to <a href="mailto:' . $mailTo . '"
                                                            rel="noopener"
                                                            style="text-decoration: none; color: #0068a5;"
                                                            target="_blank"
                                                            title="' . $mailTo . '">' . $mailTo . '</a>
                                                    </p>
                                                    <p style="margin: 0;">All rights reserved &copy; 2020. Blogpay.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table border="0" cellpadding="10" cellspacing="0"
                                        class="social_block block-5" role="presentation"
                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                                        width="100%">
                                        <tr>
                                            <td class="pad">
                                                <div align="center" class="alignment">
                                                    <table border="0" cellpadding="0" cellspacing="0"
                                                        class="social-table" role="presentation"
                                                        style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block;"
                                                        width="144px">
                                                        <tr>
                                                            <td style="padding:0 2px 0 2px;"><a
                                                                    href="https://www.facebook.com/myblogpay"
                                                                    target="_blank"><img alt="Facebook"
                                                                        height="32"
                                                                        src="https://myblogpay.com/public/img/facebook2x.png"
                                                                        style="display: block; height: auto; border: 0;"
                                                                        title="facebook"
                                                                        width="32" /></a></td>
                                                            <td style="padding:0 2px 0 2px;"><a
                                                                    href="https://www.twitter.com/myblogpay"
                                                                    target="_blank"><img alt="Twitter"
                                                                        height="32"
                                                                        src="https://myblogpay.com/public/img/twitter2x.png"
                                                                        style="display: block; height: auto; border: 0;"
                                                                        title="twitter"
                                                                        width="32" /></a></td>
                                                            <td style="padding:0 2px 0 2px;"><a
                                                                    href="https://www.linkedin.com/myblogpay"
                                                                    target="_blank"><img alt="Linkedin"
                                                                        height="32"
                                                                        src="https://myblogpay.com/public/img/linkedin2x.png"
                                                                        style="display: block; height: auto; border: 0;"
                                                                        title="linkedin"
                                                                        width="32" /></a></td>
                                                            <td style="padding:0 2px 0 2px;"><a
                                                                    href="https://www.instagram.com/myblogpay"
                                                                    target="_blank"><img alt="Instagram"
                                                                        height="32"
                                                                        src="https://myblogpay.com/public/img/instagram2x.png"
                                                                        style="display: block; height: auto; border: 0;"
                                                                        title="instagram"
                                                                        width="32" /></a></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-2"
role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
<tbody>
<tr>
<td>
<table align="left" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000; width: 480px;"
    width="480">
    <tbody>
        <tr>
            <td class="column column-1"
                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;"
                width="100%">
                <table border="0" cellpadding="0" cellspacing="0"
                    class="icons_block block-1" role="presentation"
                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                    width="100%">
                    <tr>
                        <td class="pad"
                            style="vertical-align: middle; color: #9d9d9d; font-family: inherit; font-size: 15px; padding-bottom: 5px; padding-top: 5px; text-align: center;">
                            <table cellpadding="0" cellspacing="0"
                                role="presentation"
                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"
                                width="100%">
                                <tr>
                                    <td class="alignment"
                                        style="vertical-align: middle; text-align: center;">
                                        <!--[if vml]><table align="left" cellpadding="0" cellspacing="0" role="presentation" style="display:inline-block;padding-left:0px;padding-right:0px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><![endif]-->
                                        <!--[if !vml]><!-->
                                        <table cellpadding="0" cellspacing="0"
                                            class="icons-inner" role="presentation"
                                            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; margin-right: -4px; padding-left: 0px; padding-right: 0px;">
                                            <!--<![endif]-->
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table><!-- End -->
</body>

</html>';

        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }
}