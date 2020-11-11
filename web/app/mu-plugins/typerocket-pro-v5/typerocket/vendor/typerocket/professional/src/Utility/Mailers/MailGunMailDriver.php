<?php
namespace TypeRocketPro\Utility\Mailers;

use TypeRocket\Core\Config;
use TypeRocketPro\Utility\Log;

/*
* mailgun-wordpress-plugin - Sending mail from Wordpress using Mailgun
* Copyright (C) 2016 Mailgun, et al.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/
class MailGunMailDriver implements MailDriver
{
    /**
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param string|array $headers
     * @param array $attachments
     *
     * @return bool
     */
    public function send($to, $subject, $message, $headers = '', $attachments = []) : bool
    {
        // Compact the input, apply the filters, and extract them back out
        extract(apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments')));

        $mailgun = get_option('mailgun');
        $region = Config::get('mail.mailers.mailgun.region', $mailgun['region'] ?? null);
        $apiKey = Config::get('mail.mailers.mailgun.api_key', $mailgun['api_key'] ?? null);
        $domain = Config::get('mail.mailers.mailgun.domain', $mailgun['domain'] ?? null);

        if (empty($apiKey) || empty($domain)) {
            Log::critical('[Mailgun] No API Key or domain set.');
            return false;
        }

        // If a region is not set via defines or through the options page, default to US region.
        if (!((bool) $region)) {
            Log::warning('[Mailgun] No region configuration was found! Defaulting to US region.');
            $region = 'us';
        }

        if (!is_array($attachments)) {
            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
        }

        // Headers
        if (empty($headers)) {
            $headers = array();
        } else {
            if (!is_array($headers)) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }
            $headers = array();
            $cc = array();
            $bcc = array();

            // If it's actually got contents
            if (!empty($tempheaders)) {
                // Iterate through the raw headers
                foreach ((array) $tempheaders as $header) {
                    if (strpos($header, ':') === false) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out
                    [$name, $content] = explode(':', trim($header), 2);

                    // Cleanup crew
                    $name = trim($name);
                    $content = trim($content);

                    switch (strtolower($name)) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            if (strpos($content, '<') !== false) {
                                // So... making my life hard again?
                                $from_name = substr($content, 0, strpos($content, '<') - 1);
                                $from_name = str_replace('"', '', $from_name);
                                $from_name = trim($from_name);

                                $from_email = substr($content, strpos($content, '<') + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);
                            } else {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if (strpos($content, ';') !== false) {
                                [$type, $charset] = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset, 'charset=')) {
                                    $charset = trim(str_replace(array('charset=', '"'), '', $charset));
                                } elseif (false !== stripos($charset, 'boundary=')) {
                                    $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset));
                                    $charset = '';
                                }
                            } else {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array) $cc, explode(',', $content));
                            break;
                        case 'bcc':
                            $bcc = array_merge((array) $bcc, explode(',', $content));
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }
        }

        if (!isset($from_name)) {
            $from_name = null;
        }

        if (!isset($from_email)) {
            $from_email = null;
        }

        $from_name = $this->mg_detect_from_name($from_name);
        $from_email = $this->mg_detect_from_address($from_email);

        $body = array(
            'from'    => "{$from_name} <{$from_email}>",
            'to'      => $to,
            'subject' => $subject,
        );

        $rcpt_data = apply_filters('mg_mutate_to_rcpt_vars', $this->mg_mutate_to_rcpt_vars($to));
        if (!is_null($rcpt_data['rcpt_vars'])) {
            $body['recipient-variables'] = $rcpt_data['rcpt_vars'];
        }

        $body['o:tag'] = array();
        $body['o:tracking-clicks'] = !empty($mailgun['track-clicks']) ? $mailgun['track-clicks'] : 'no';
        $body['o:tracking-opens'] = empty($mailgun['track-opens']) ? 'no' : 'yes';

        // this is the wordpress site tag
        if (isset($mailgun['tag'])) {
            $tags = explode(',', str_replace(' ', '', $mailgun['tag']));
            $body['o:tag'] = $tags;
        }

        // campaign-id now refers to a list of tags which will be appended to the site tag
        if (!empty($mailgun['campaign-id'])) {
            $tags = explode(',', str_replace(' ', '', $mailgun['campaign-id']));
            if (empty($body['o:tag'])) {
                $body['o:tag'] = $tags;
            } elseif (is_array($body['o:tag'])) {
                $body['o:tag'] = array_merge($body['o:tag'], $tags);
            } else {
                $body['o:tag'] .= ','.$tags;
            }
        }

        if (!empty($cc) && is_array($cc)) {
            $body['cc'] = implode(', ', $cc);
        }

        if (!empty($bcc) && is_array($bcc)) {
            $body['bcc'] = implode(', ', $bcc);
        }

        // If we are not given a Content-Type in the supplied headers,
        // write the message body to a file and try to determine the mimetype
        // using get_mime_content_type.
        if (!isset($content_type)) {
            $tmppath = tempnam(sys_get_temp_dir(), 'mg');
            $tmp = fopen($tmppath, 'w+');

            fwrite($tmp, $message);
            fclose($tmp);

            $content_type = $this->mg_get_mime_content_type($tmppath, 'text/plain');

            unlink($tmppath);
        }

        // Allow external content type filter to function normally
        if (has_filter('wp_mail_content_type')) {
            $content_type = apply_filters(
                'wp_mail_content_type',
                $content_type
            );
        }

        if ('text/plain' === $content_type) {
            $body['text'] = $message;
        } else if ('text/html' === $content_type) {
            $body['html'] = $message;
        } else {
            // Unknown Content-Type??
            Log::critical('[mailgun] Got unknown Content-Type: ' . $content_type);
            $body['text'] = $message;
            $body['html'] = $message;
        }

        // If we don't have a charset from the input headers
        if (!isset($charset)) {
            $charset = get_bloginfo('charset');
        }

        // Set the content-type and charset
        $charset = apply_filters('wp_mail_charset', $charset);
        if (isset($headers['Content-Type'])) {
            if (!strstr($headers['Content-Type'], 'charset')) {
                $headers['Content-Type'] = rtrim($headers['Content-Type'], '; ')."; charset={$charset}";
            }
        }

        // Set custom headers
        if (!empty($headers)) {
            foreach ((array) $headers as $name => $content) {
                $body["h:{$name}"] = $content;
            }

            // TODO: Can we handle this?
            //if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
            //  $phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
        }

        /*
         * Deconstruct post array and create POST payload.
         * This entire routine is because wp_remote_post does
         * not support files directly.
         */

        $payload = '';

        // First, generate a boundary for the multipart message.
        $boundary = 'boundary-' . bin2hex(random_bytes(11));

        // Allow other plugins to apply body changes before creating the payload.
        $body = apply_filters('mg_mutate_message_body', $body);
        if ( ($body_payload = $this->mg_build_payload_from_body($body, $boundary)) != null ) {
            $payload .= $body_payload;
        }

        // TODO: Special handling for multipart/alternative mail
        // if ('multipart/alternative' === $content_type) {
        //     // Build payload from mime
        //     // error_log(sprintf('building message payload from multipart/alternative'));
        //     // error_log($body['message']);
        //     // error_log('Attachments:');
        //     // foreach ($attachments as $attachment) {
        //     //     error_log($attachment);
        //     // }
        // }

        // Allow other plugins to apply attachment changes before writing to the payload.
        $attachments = apply_filters('mg_mutate_attachments', $attachments);
        if ( ($attachment_payload = $this->mg_build_attachments_payload($attachments, $boundary)) != null ) {
            $payload .= $attachment_payload;
        }

        $payload .= '--'.$boundary.'--';

        $data = array(
            'body'    => $payload,
            'headers' => array(
                'Authorization' => 'Basic '.base64_encode("api:{$apiKey}"),
                'Content-Type'  => 'multipart/form-data; boundary='.$boundary,
            ),
        );

        $endpoint = $this->mg_api_get_region($region);
        $endpoint = ($endpoint) ? $endpoint : 'https://api.mailgun.net/v3/';
        $url = $endpoint."{$domain}/messages";

        // TODO: Mailgun only supports 1000 recipients per request, since we are
        // overriding this function, let's add looping here to handle that
        $response = wp_remote_post($url, $data);
        if (is_wp_error($response)) {
            // Store WP error in last error.
            $this->mg_api_last_error($response->get_error_message());

            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response));

        // Mailgun API should *always* return a `message` field, even when
        // $response_code != 200, so a lack of `message` indicates something
        // is broken.
        if ((int) $response_code != 200 && !isset($response_body->message)) {
            // Store response code and HTTP response message in last error.
            $response_message = wp_remote_retrieve_response_message($response);
            $errmsg = "$response_code - $response_message";
            $this->mg_api_last_error($errmsg);

            return false;
        }

        // Not sure there is any additional checking that needs to be done here, but why not?
        if ($response_body->message != 'Queued. Thank you.') {
            $this->mg_api_last_error($response_body->message);

            return false;
        }

        return true;
    }

    function mg_build_payload_from_body($body, $boundary) {
        $payload = '';

        // Iterate through pre-built params and build payload:
        foreach ($body as $key => $value) {
            if (is_array($value)) {
                $parent_key = $key;
                foreach ($value as $key => $value) {
                    $payload .= '--'.$boundary;
                    $payload .= "\r\n";
                    $payload .= 'Content-Disposition: form-data; name="'.$parent_key."\"\r\n\r\n";
                    $payload .= $value;
                    $payload .= "\r\n";
                }
            } else {
                $payload .= '--'.$boundary;
                $payload .= "\r\n";
                $payload .= 'Content-Disposition: form-data; name="'.$key.'"'."\r\n\r\n";
                $payload .= $value;
                $payload .= "\r\n";
            }
        }

        return $payload;
    }

    function mg_build_payload_from_mime($body, $boundary) {
    }

    function mg_build_attachments_payload($attachments, $boundary) {
        $payload = '';

        // If we have attachments, add them to the payload.
        if (!empty($attachments)) {
            $i = 0;
            foreach ($attachments as $attachment) {
                if (!empty($attachment)) {
                    $payload .= '--'.$boundary;
                    $payload .= "\r\n";
                    $payload .= 'Content-Disposition: form-data; name="attachment['.$i.']"; filename="'.basename($attachment).'"'."\r\n\r\n";
                    $payload .= file_get_contents($attachment);
                    $payload .= "\r\n";
                    $i++;
                }
            }
        } else {
            return null;
        }

        return $payload;
    }

    /**
     * mg_smtp_last_error is a compound getter/setter for the last error that was
     * encountered during a Mailgun SMTP conversation.
     *
     * @param string $error OPTIONAL
     *
     * @return string Last error that occurred.
     *
     * @since 1.5.0
     */
    function mg_smtp_last_error($error = null)
    {
        static $last_error;

        if (null === $error) {
            return $last_error;
        } else {
            $tmp = $last_error;
            $last_error = $error;

            return $tmp;
        }
    }

    /**
     * Debugging output function for PHPMailer.
     *
     * @param string $str   Log message
     * @param string $level Logging level
     *
     * @return void
     *
     * @since 1.5.7
     */
    function mg_smtp_debug_output($str, $level)
    {
        if (defined('MG_DEBUG_SMTP') && MG_DEBUG_SMTP) {
            Log::debug("PHPMailer [$level] $str");
        }
    }

    /**
     * Capture and store the failure message from PHPmailer so the user will
     * actually know what is wrong.
     *
     * @param \WP_Error $error Error raised by Wordpress/PHPmailer
     *
     * @return void
     *
     * @since 1.5.7
     */
    function wp_mail_failed($error)
    {
        if (is_wp_error($error)) {
            $this->mg_smtp_last_error($error->get_error_message());
        } else {
            $this->mg_smtp_last_error($error->__toString());
        }
    }

    /**
     * Provides a `wp_mail` compatible filter for SMTP sends through the
     * Wordpress PHPmailer transport.
     *
     * @param array $args Compacted array of arguments.
     *
     * @return array Compacted array of arguments.
     *
     * @since 1.5.8
     */
    function mg_smtp_mail_filter(array $args)
    {
        // Extract the arguments from array to ($to, $subject, $message, $headers, $attachments)
        extract($args);

        // $headers and $attachments are optional - make sure they exist
        $headers = (!isset($headers)) ? '' : $headers;
        $attachments = (!isset($attachments)) ? array() : $attachments;

        $mg_headers = $this->mg_parse_headers($headers);

        // Filter the `From:` header
        $from_header = (isset($mg_headers['From'])) ? $mg_headers['From'][0] : null;

        [$from_name, $from_addr] = array(null, null);
        if (!is_null($from_header)) {
            $content = $from_header['value'];
            $boundary = $from_header['boundary'];
            $parts = $from_header['parts'];

            if (strpos($content, '<') !== false) {
                $from_name = substr($content, 0, strpos($content, '<') - 1);
                $from_name = str_replace('"', '', $from_name);
                $from_name = trim($from_name);

                $from_addr = substr($content, strpos($content, '<') + 1);
                $from_addr = str_replace('>', '', $from_addr);
                $from_addr = trim($from_addr);
            } else {
                $from_addr = trim($content);
            }
        }

        if (!isset($from_name)) {
            $from_name = null;
        }

        if (!isset($from_addr)) {
            $from_addr = null;
        }

        $from_name = $this->mg_detect_from_name($from_name);
        $from_addr = $this->mg_detect_from_address($from_addr);

        $from_header['value'] = sprintf('%s <%s>', $from_name, $from_addr);
        $mg_headers['From'] = array($from_header);

        // Header compaction
        $headers = $this->mg_dump_headers($mg_headers);

        return compact('to', 'subject', 'message', 'headers', 'attachments');
    }

    /**
     * mg_api_last_error is a compound getter/setter for the last error that was
     * encountered during a Mailgun API call.
     *
     * @param	string	$error	OPTIONAL
     *
     * @return	string	Last error that occurred.
     *
     * @since	1.5.0
     */
    function mg_api_last_error($error = null)
    {
        static $last_error;

        Log::critical('[Mailgun] ' . $error);

        if (null === $error) {
            return $last_error;
        } else {
            $tmp = $last_error;
            $last_error = $error;

            return $tmp;
        }
    }

    function mg_mutate_to_rcpt_vars($to_addrs)
    {
        if (is_string($to_addrs)) {
            $to_addrs = explode(',', $to_addrs);
        }

        if (has_filter('mg_use_recipient_vars_syntax')) {
            $use_rcpt_vars = apply_filters('mg_use_recipient_vars_syntax', null);
            if ($use_rcpt_vars) {
                $vars = array();

                $idx = 0;
                foreach ($to_addrs as $addr) {
                    $rcpt_vars[$addr] = array('batch_msg_id' => $idx);
                    $idx++;
                }

                // TODO: Also add folding to prevent hitting the 998 char limit on headers.
                return array(
                    'to'        => '%recipient%',
                    'rcpt_vars' => json_encode($rcpt_vars),
                );
            }
        }

        return array(
            'to'        => $to_addrs,
            'rcpt_vars' => null,
        );
    }

    /**
     * Tries several methods to get the MIME Content-Type of a file.
     *
     * @param	string	$filepath
     * @param	string	$default_type	If all methods fail, fallback to $default_type
     *
     * @return	string	Content-Type
     *
     * @since	1.5.4
     */
    function mg_get_mime_content_type($filepath, $default_type = 'text/plain')
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filepath);
        } elseif (function_exists('finfo_file')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $ret = finfo_file($fi, $filepath);
            finfo_close($fi);

            return $ret;
        } else {
            return $default_type;
        }
    }

    /**
     * Find the sending "From Name" with a similar process used in `wp_mail`.
     * This operates as a filter for the from name. If the override is set,
     * a given name will clobbered except in ONE case.
     * If the override is not enabled this is the from name resolution order:
     *  1. From name given by headers - {@param $from_name_header}
     *  2. From name set in Mailgun settings
     *  3. From `MAILGUN_FROM_NAME` constant
     *  4. From name constructed as `<your_site_title>` or "WordPress"
     *
     * If the `wp_mail_from` filter is available, it is applied to the resulting
     * `$from_addr` before being returned. The filtered result is null-tested
     * before being returned.
     *
     * @return	string
     *
     * @since	1.5.8
     */
    function mg_detect_from_name($from_name_header = null)
    {
        // Get options to avoid strict mode problems
        $mg_opts = get_option('mailgun');
        $mg_override_from = Config::get('mail.mailers.mailgun.from_override', $mg_opts['from_override'] ?? null);
        $mg_from_name = Config::get('mail.mailers.mailgun.from_name', $mg_opts['from_name'] ?? null);

        $from_name = null;

        if ($mg_override_from && !is_null($mg_from_name)) {
            $from_name = $mg_from_name;
        } elseif (!is_null($from_name_header)) {
            $from_name = $from_name_header;
        } else {
            if (is_null($mg_from_name) || empty($mg_from_name)) {
                if (function_exists('get_current_site')) {
                    $from_name = get_current_site()->site_name;
                } else {
                    $from_name = 'WordPress';
                }
            } else {
                $from_name = $mg_from_name;
            }
        }

        $filter_from_name = null;
        if (has_filter('wp_mail_from_name')) {
            $filter_from_name = apply_filters(
                'wp_mail_from_name',
                $from_name
            );
            if (!is_null($filter_from_name) && !empty($filter_from_name)) {
                $from_name = $filter_from_name;
            }
        }

        return $from_name;
    }

    /**
     * Find the sending "From Address" with a similar process used in `wp_mail`.
     * This operates as a filter for the from address. If the override is set,
     * a given address will except in ONE case.
     * If the override is not enabled this is the from address resolution order:
     *  1. From address given by headers - {@param $from_addr_header}
     *  2. From address set in Mailgun settings
     *  3. From `MAILGUN_FROM_ADDRESS` constant
     *  4. From address constructed as `wordpress@<your_site_domain>`
     *
     * If the `wp_mail_from` filter is available, it is applied to the resulting
     * `$from_addr` before being returned. The filtered result is null-tested
     * before being returned.
     *
     * If we don't have `From` input headers, use wordpress@$sitedomain
     * Some hosts will block outgoing mail from this address if it doesn't
     * exist but there's no easy alternative. Defaulting to admin_email
     * might appear to be another option but some hosts may refuse to
     * relay mail from an unknown domain.
     *
     * @link	http://trac.wordpress.org/ticket/5007.
     *
     * @return	string
     *
     * @since	1.5.8
     */
    function mg_detect_from_address($from_addr_header = null)
    {
        // Get options to avoid strict mode problems
        $mg_opts = get_option('mailgun');
        $mg_override_from = Config::get('mail.mailers.mailgun.from_override', $mg_opts['override_from'] ?? null);
        $mg_from_addr = Config::get('mail.mailers.mailgun.from_address', $mg_opts['from_address'] ?? null);

        $from_addr = null;

        if ($mg_override_from && !is_null($mg_from_addr)) {
            $from_addr = $mg_from_addr;
        } elseif (!is_null($from_addr_header)) {
            $from_addr = $from_addr_header;
        } else {
            if (is_null($mg_from_addr) || empty($mg_from_addr)) {
                if (function_exists('get_current_site')) {
                    $sitedomain = get_current_site()->domain;
                } else {
                    $sitedomain = strtolower($_SERVER['SERVER_NAME']);
                    if (substr($sitedomain, 0, 4) === 'www.') {
                        $sitedomain = substr($sitedomain, 4);
                    }
                }

                $from_addr = 'wordpress@'.$sitedomain;
            } else {
                $from_addr = $mg_from_addr;
            }
        }

        $filter_from_addr = null;
        if (has_filter('wp_mail_from')) {
            $filter_from_addr = apply_filters(
                'wp_mail_from',
                $from_addr
            );
            if (!is_null($filter_from_addr) || !empty($filter_from_addr)) {
                $from_addr = $filter_from_addr;
            }
        }

        return $from_addr;
    }

    /**
     * Parses mail headers into an array of arrays so they can be easily modified.
     * We have to deal with headers that may have boundaries or parts, so a single
     * header like:
     *
     *  From: Excited User <user@samples.mailgun.com>
     *
     * Will look like this in array format:
     *
     *  array(
     *      'from' => array(
     *          'value'    => 'Excited User <user@samples.mailgun.com>',
     *          'boundary' => null,
     *          'parts'    => null,
     *      )
     *  )
     *
     * @param	string|array	$headers
     *
     * @return	array
     *
     * @since	1.5.8
     */
    function mg_parse_headers($headers = array())
    {
        if (empty($headers)) {
            return array();
        }

        $tmp = array();
        if (!is_array($headers)) {
            $tmp = explode("\n", str_replace("\r\n", "\n", $headers));
        } else {
            $tmp = $headers;
        }

        $new_headers = array();
        if (!empty($tmp)) {
            $name = null;
            $value = null;
            $boundary = null;
            $parts = null;

            foreach ((array) $tmp as $header) {
                // If this header does not contain a ':', is it a fold?
                if (false === strpos($header, ':')) {
                    // Does this header have a boundary?
                    if (false !== stripos($header, 'boundary=')) {
                        $parts = preg_split('/boundary=/i', trim($header));
                        $boundary = trim(str_replace(array('"', '\''), '', $parts[1]));
                    }
                    $value .= $header;

                    continue;
                }

                // Explode the header
                [$name, $value] = explode(':', trim($header), 2);

                // Clean up the values
                $name = trim($name);
                $value = trim($value);

                if ( !isset($new_headers[$name]) ) {
                    $new_headers[$name] = array();
                }

                array_push($new_headers[$name], array(
                    'value'     => $value,
                    'boundary'  => $boundary,
                    'parts'     => $parts,
                ));
            }
        }

        return $new_headers;
    }

    /**
     * Takes a header array in the format produced by mg_parse_headers and
     * dumps them down in to a submittable header format.
     *
     * @param	array	$headers	Headers to dump
     *
     * @return	string	String of \r\n separated headers
     *
     * @since	1.5.8
     */
    function mg_dump_headers($headers = null)
    {
        if (is_null($headers) || !is_array($headers)) {
            return '';
        }

        $header_string = '';
        foreach ($headers as $name => $values) {
            $header_string .= sprintf("%s: ", $name);
            $header_values = array();

            foreach ($values as $content) {
                // XXX - Is it actually okay to discard `parts` and `boundary`?
                array_push($header_values, $content['value']);
            }

            $header_string .= sprintf("%s\r\n", implode(", ", $header_values));
        }

        return $header_string;
    }

    /**
     * Set the API endpoint based on the region selected.
     * Value can be "0" if not selected, "us" or "eu"
     *
     * @param	string	$getRegion	Region value set either in config or Mailgun plugin settings.
     *
     * @return	bool|string
     *
     * @since	1.5.12
     */
    function mg_api_get_region($getRegion)
    {
        switch ($getRegion) {
            case 'us': return 'https://api.mailgun.net/v3/';
            case 'eu': return 'https://api.eu.mailgun.net/v3/';
            default: return false;
        }
    }

    /**
     * Set the SMTP endpoint based on the region selected.
     * Value can be "0" if not selected, "us" or "eu"
     *
     * @param	string	$getRegion	Region value set either in config or Mailgun plugin settings.
     *
     * @return	bool|string
     *
     * @since	1.5.12
     */
    function mg_smtp_get_region($getRegion)
    {
        switch ($getRegion) {
            case 'us': return 'smtp.mailgun.org';
            case 'eu': return 'smtp.eu.mailgun.org';
            default: return false;
        }
    }
}