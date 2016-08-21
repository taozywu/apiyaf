<?php 
/**
 * 1.校验format
 * 2.校验method
 * 3.校验黑名单
 * 4.对get，post等获取参数的处理及校验
 * 5.对接口class，method做校验 ---@todo
 * 6.校验白名单
 * 7.校验key       --- @todo
 * 8.对接口次数，访问接口时间间隔限制。---@todo
 */
namespace Core\Controller;

class Base extends \Yaf\Controller_Abstract
{
    /**
     * config
     * @var null
     */
    protected $_conf = null;

    protected $output = null;

    protected $format = null;

    /**
     * This defines the rest format
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $rest_format = NULL;
    /**
     * Defines the list of method properties such as limit, log and level
     *
     * @var array
     */
    protected $methods = [];
    /**
     * List of allowed HTTP methods
     *
     * @var array
     */
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];
    /**
     * Contains details about the request
     * Fields: body, format, method, ssl
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $request = NULL;
    /**
     * Contains details about the response
     * Fields: format, lang
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $response = NULL;
    /**
     * Contains details about the REST API
     * Fields: db, ignore_limits, key, level, user_id
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $rest = NULL;
    /**
     * The arguments for the GET request method
     *
     * @var array
     */
    protected $_get_args = [];
    /**
     * The arguments for the POST request method
     *
     * @var array
     */
    protected $_post_args = [];
    /**
     * The arguments for the PUT request method
     *
     * @var array
     */
    protected $_put_args = [];
    /**
     * The arguments for the DELETE request method
     *
     * @var array
     */
    protected $_delete_args = [];
    /**
     * The arguments for the PATCH request method
     *
     * @var array
     */
    protected $_patch_args = [];
    /**
     * The arguments for the HEAD request method
     *
     * @var array
     */
    protected $_head_args = [];
    /**
     * The arguments for the OPTIONS request method
     *
     * @var array
     */
    protected $_options_args = [];
    /**
     * The arguments for the query parameters
     *
     * @var array
     */
    protected $_query_args = [];
    /**
     * The arguments from GET, POST, PUT, DELETE, PATCH, HEAD and OPTIONS request methods combined
     *
     * @var array
     */
    protected $_args = [];
    /**
     * The insert_id of the log entry (if we have one)
     *
     * @var string
     */
    protected $_insert_id = '';
    /**
     * If the request is allowed based on the API key provided
     *
     * @var bool
     */
    protected $_allow = TRUE;
    /**
     * The LDAP Distinguished Name of the User post authentication
     *
     * @var string
     */
    protected $_user_ldap_dn = '';
    /**
     * The start of the response time from the server
     *
     * @var string
     */
    protected $_start_rtime = '';
    /**
     * The end of the response time from the server
     *
     * @var string
     */
    protected $_end_rtime = '';
    /**
     * List all supported methods, the first will be the default format
     *
     * @var array
     */
    protected $_supported_formats = [
            'json' => 'application/json',
            'array' => 'application/json',
            'csv' => 'application/csv',
            'html' => 'text/html',
            'jsonp' => 'application/javascript',
            'php' => 'text/plain',
            'serialized' => 'application/vnd.php.serialized',
            'xml' => 'application/xml'
        ];
    /**
     * Information about the current API user
     *
     * @var object
     */
    protected $_apiuser;
    /**
     * Enable XSS flag
     * Determines whether the XSS filter is always active when
     * GET, OPTIONS, HEAD, POST, PUT, DELETE and PATCH data is encountered.
     * Set automatically based on config setting
     *
     * @var bool
     */
    protected $_enable_xss = FALSE;

    protected $input = null;
    /**
     * HTTP status codes and their respective description
     * Note: Only the widely used HTTP status codes are used
     *
     * @var array
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    // protected $http_status_codes = [
    //     self::HTTP_OK => 'OK',
    //     self::HTTP_CREATED => 'CREATED',
    //     self::HTTP_NO_CONTENT => 'NO CONTENT',
    //     self::HTTP_NOT_MODIFIED => 'NOT MODIFIED',
    //     self::HTTP_BAD_REQUEST => 'BAD REQUEST',
    //     self::HTTP_UNAUTHORIZED => 'UNAUTHORIZED',
    //     self::HTTP_FORBIDDEN => 'FORBIDDEN',
    //     self::HTTP_NOT_FOUND => 'NOT FOUND',
    //     self::HTTP_METHOD_NOT_ALLOWED => 'METHOD NOT ALLOWED',
    //     self::HTTP_NOT_ACCEPTABLE => 'NOT ACCEPTABLE',
    //     self::HTTP_CONFLICT => 'CONFLICT',
    //     self::HTTP_INTERNAL_SERVER_ERROR => 'INTERNAL SERVER ERROR',
    //     self::HTTP_NOT_IMPLEMENTED => 'NOT IMPLEMENTED'
    // ];
    // 
    // $this->rest = new \stdClass();

    // 初始化
    public function init()
    {
        // Disable XML Entity (security vulnerability)
        libxml_disable_entity_loader(true);

        // 给接口禁止掉view
        \Yaf\Dispatcher::getInstance()->disableView();

        // init object
        $this->_initObj();

        $this->_enable_xss = ($this->_conf['system']['global_xss_filtering'] === TRUE);

        // check support format.
        $this->_checkSupportFormats();

        // check ip black list.
        if ($this->_conf['system']['rest_ip_blacklist_enabled']) {
            $this->_checkIpBlackList();
        }

        $this->request->method = $this->request->method ? strtolower($this->request->method) : "get";

        // check method allowed.
        if ($this->_conf['system']['rest_method_allowed_enabled']) {
            $this->_checkMethodAllowed();
        }

        $this->request->ssl = \Helper\String::isHttps();
        
        // Set up the query parameters
        $this->_parse_query();
        // Set up the GET variables
        // $this->_get_args = array_merge($this->_get_args, $this->uri->ruri_to_assoc());
        // Try to find a format for the request (means we have a request body)
        $this->request->format = $this->_detect_input_format();
        // Not all methods have a body attached with them
        $this->request->body = NULL;
        $this->{'_parse_' . $this->request->method}();
        // Which format should the data be returned in?
        $this->response->format = $this->_detect_output_format();
        // Extend this function to apply additional checking early on in the process
        $this->early_checks();
        // Check if there is a specific auth type for the current class/method
        // _auth_override_check could exit so we need $this->rest->db initialized before
        $this->auth_override = $this->_auth_override_check();
        // Checking for keys? GET TO WorK!
        // Skip keys test for $config['auth_override_class_method']['class'['method'] = 'none'
        if ($this->_conf['system']['rest_enable_keys'] && $this->auth_override !== TRUE)
        {
            $this->_allow = $this->_detect_api_key();
        }
        // Only allow ajax requests
        if ($this->input->is_ajax_request() === FALSE && $this->_conf['system']['rest_ajax_only'])
        {
            // Display an error response
            $this->response([
                    $this->_conf['system']['rest_status_field_name'] => FALSE,
                    $this->_conf['system']['rest_message_field_name'] => $this->_conf['system']['lang']['text_rest_ajax_only']
                ], \Helper\Http::HTTP_NOT_ACCEPTABLE);
        }

        // When there is no specific override for the current class/method, use the default auth value set in the config
        if ($this->auth_override === FALSE && !($this->_conf['system']['rest_enable_keys'] && $this->_allow === TRUE))
        {
            $rest_auth = strtolower($this->_conf['system']['rest_auth']);
            switch ($rest_auth)
            {
                case 'basic':
                    $this->_prepare_basic_auth();
                    break;
                case 'digest':
                    $this->_prepare_digest_auth();
                    break;
                case 'session':
                    $this->_check_php_session();
                    break;
                default:
                    break;
            }
            if ($this->_conf['system']['rest_ip_whitelist_enabled'] === TRUE)
            {
                $this->_check_whitelist_auth();
            }
        }


    }

    /**
     * init object.
     * @return [type] [description]
     */
    private function _initObj()
    {
        !$this->_conf && $this->_conf = \Yaf\Registry::get("config");
        !$this->request && $this->request = $this->getRequest();
        !$this->response && $this->response = $this->getResponse();
        !$this->output && $this->output = new \CI\Output();
        !$this->format && $this->format = new \CI\Format();
        !$this->input && $this->input = new \CI\Input();
    }


    /**
     * Get the input format e.g. json or xml
     *
     * @access protected
     * @return string|NULL Supported input format; otherwise, NULL
     */
    protected function _detect_input_format()
    {
        // Get the CONTENT-TYPE value from the SERVER variable
        $content_type = $this->input->server('CONTENT_TYPE');
        if (empty($content_type) === FALSE)
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach ($this->_supported_formats as $key => $value)
            {
                // $key = format e.g. csv
                // $value = mime type e.g. application/csv
                // If a semi-colon exists in the string, then explode by ; and get the value of where
                // the current array pointer resides. This will generally be the first element of the array
                $content_type = (strpos($content_type, ';') !== FALSE ? current(explode(';', $content_type)) : $content_type);
                // If both the mime types match, then return the format
                if ($content_type === $value)
                {
                    return $key;
                }
            }
        }
        return NULL;
    }


    /**
     * Detect which format should be used to output the data
     *
     * @access protected
     * @return mixed|NULL|string Output format
     */
    protected function _detect_output_format()
    {
        // Concatenate formats to a regex pattern e.g. \.(csv|json|xml)
        $pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')($|\/)/';
        $matches = [];
        // Check if a file extension is used e.g. http://example.com/api/index.json?param1=param2
        // if (preg_match($pattern, $this->uri->uri_string(), $matches))
        // {
        //     return $matches[1];
        // }
        // Get the format parameter named as 'format'
        if (isset($this->_get_args['format']))
        {
            $format = strtolower($this->_get_args['format']);
            if (isset($this->_supported_formats[$format]) === TRUE)
            {
                return $format;
            }
        }
        // Get the HTTP_ACCEPT server variable
        $http_accept = $this->input->server('HTTP_ACCEPT');
        // Otherwise, check the HTTP_ACCEPT server variable
        if ($this->_conf['system']['rest_ignore_http_accept'] === FALSE && $http_accept !== NULL)
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) as $format)
            {
                // Has this format been requested?
                if (strpos($http_accept, $format) !== FALSE)
                {
                    if ($format !== 'html' && $format !== 'xml')
                    {
                        // If not HTML or XML assume it's correct
                        return $format;
                    }
                    elseif ($format === 'html' && strpos($http_accept, 'xml') === FALSE)
                    {
                        // HTML or XML have shown up as a match
                        // If it is truly HTML, it wont want any XML
                        return $format;
                    }
                    else if ($format === 'xml' && strpos($http_accept, 'html') === FALSE)
                    {
                        // If it is truly XML, it wont want any HTML
                        return $format;
                    }
                }
            }
        }
        // Check if the controller has a default format
        if (empty($this->rest_format) === FALSE)
        {
            return $this->rest_format;
        }
        // Obtain the default format from the configuration
        return $this->_get_default_output_format();
    }

    /**
     * Takes mixed data and optionally a status code, then creates the response
     *
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     * @param bool $continue TRUE to flush the response to the client and continue
     * running the script; otherwise, exit
     */
    public function response($data = NULL, $http_code = NULL, $continue = FALSE)
    {
        // If the HTTP status is not NULL, then cast as an integer
        if ($http_code !== NULL)
        {
            // So as to be safe later on in the process
            $http_code = (int) $http_code;
        }
        // Set the output as NULL by default
        $output = NULL;
        // If data is NULL and no HTTP status code provided, then display, error and exit
        if ($data === NULL && $http_code === NULL)
        {
            $http_code = \Helper\Http::HTTP_NOT_FOUND;
        }
        // If data is not NULL and a HTTP status code provided, then continue
        elseif ($data !== NULL)
        {
            // If the format method exists, call and return the output in that format
            if (method_exists($this->format, 'to_' . $this->response->format))
            {
                // Set the format header
                $this->output->set_content_type($this->_supported_formats[$this->response->format], strtolower($this->_conf['system']['charset']));
                $output = $this->format->factory($data)->{'to_' . $this->response->format}();
                // An array must be parsed as a string, so as not to cause an array to string error
                // Json is the most appropriate form for such a datatype
                if ($this->response->format === 'array') {
                    $output = $this->format->factory($output)->{'to_json'}();
                }
            }
            else
            {
                // If an array or object, then parse as a json, so as to be a 'string'
                if (is_array($data) || is_object($data))
                {
                    $data = $this->format->factory($data)->to_json();
                }
                // Format is not supported, so output the raw data as a string
                $output = $data;
            }
        }
        // If not greater than zero, then set the HTTP status code as 200 by default
        // Though perhaps 500 should be set instead, for the developer not passing a
        // correct HTTP status code
        $http_code > 0 || $http_code = \Helper\Http::HTTP_OK;
        $this->output->set_status_header($http_code);
        $this->output->set_output($output);
        if ($continue === FALSE)
        {
            // Display the data and exit execution
            $this->output->_display();
            exit;
        }
        // Otherwise dump the output automatically
    }


     /**
     * Parse the GET request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_get()
    {
        // Merge both the URI segments and query parameters
        // @todo 将getParams的参数也放到get里面
        $this->_get_args = array_merge($this->_get_args, $this->_query_args, $this->request->getParams());
    }
    /**
     * Parse the POST request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_post()
    {
        $this->_post_args = $_POST;
        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
        }
    }
    /**
     * Parse the PUT request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_put()
    {
        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
        }
        else if ($this->input->method() === 'put')
        {
           // If no filetype is provided, then there are probably just arguments
           $this->_put_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the HEAD request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_head()
    {
        // Parse the HEAD variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $head);
        // Merge both the URI segments and HEAD params
        $this->_head_args = array_merge($this->_head_args, $head);
    }

    /**
     * Parse the OPTIONS request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_options()
    {
        // Parse the OPTIONS variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $options);
        // Merge both the URI segments and OPTIONS params
        $this->_options_args = array_merge($this->_options_args, $options);
    }

    /**
     * Parse the PATCH request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_patch()
    {
        // It might be a HTTP body
        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
        }
        else if ($this->input->method() === 'patch')
        {
            // If no filetype is provided, then there are probably just arguments
            $this->_patch_args = $this->input->input_stream();
        }
    }
    /**
     * Parse the DELETE request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_delete()
    {
        // These should exist if a DELETE request
        if ($this->input->method() === 'delete')
        {
            $this->_delete_args = $this->input->input_stream();
        }
    }
    /**
     * Parse the query parameters
     *
     * @access protected
     * @return void
     */
    protected function _parse_query()
    {
        $this->_query_args = $this->input->get();
    }


    /**
     * Retrieve a value from a GET request
     *
     * @access public
     * @param NULL $key Key to retrieve from the GET request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the GET request; otherwise, NULL
     */
    public function get($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $_GET;
        }
        return isset($this->_get_args[$key]) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a OPTIONS request
     *
     * @access public
     * @param NULL $key Key to retrieve from the OPTIONS request.
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the OPTIONS request; otherwise, NULL
     */
    public function options($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_options_args;
        }
        return isset($this->_options_args[$key]) ? $this->_xss_clean($this->_options_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a HEAD request
     *
     * @access public
     * @param NULL $key Key to retrieve from the HEAD request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the HEAD request; otherwise, NULL
     */
    public function head($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_head_args;
        }
        return isset($this->_head_args[$key]) ? $this->_xss_clean($this->_head_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a POST request
     *
     * @access public
     * @param NULL $key Key to retrieve from the POST request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the POST request; otherwise, NULL
     */
    public function post($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_post_args;
        }
        return isset($this->_post_args[$key]) ? $this->_xss_clean($this->_post_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a PUT request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PUT request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PUT request; otherwise, NULL
     */
    public function put($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_put_args;
        }
        return isset($this->_put_args[$key]) ? $this->_xss_clean($this->_put_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a DELETE request
     *
     * @access public
     * @param NULL $key Key to retrieve from the DELETE request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the DELETE request; otherwise, NULL
     */
    public function delete($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_delete_args;
        }
        return isset($this->_delete_args[$key]) ? $this->_xss_clean($this->_delete_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from a PATCH request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PATCH request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PATCH request; otherwise, NULL
     */
    public function patch($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_patch_args;
        }
        return isset($this->_patch_args[$key]) ? $this->_xss_clean($this->_patch_args[$key], $xss_clean) : NULL;
    }
    /**
     * Retrieve a value from the query parameters
     *
     * @access public
     * @param NULL $key Key to retrieve from the query parameters
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the query parameters; otherwise, NULL
     */
    public function query($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_query_args;
        }
        return isset($this->_query_args[$key]) ? $this->_xss_clean($this->_query_args[$key], $xss_clean) : NULL;
    }


    /**
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented
     *
     * @access protected
     * @param  string $value Input data
     * @param  bool $xss_clean Whether to apply XSS filtering
     * @return string
     */
    protected function _xss_clean($value, $xss_clean)
    {
        is_bool($xss_clean) || $xss_clean = $this->_enable_xss;
        return $xss_clean === TRUE ? $this->security->xss_clean($value) : $value;
    }




    /**
     * Perform LDAP Authentication
     *
     * @access protected
     * @param  string $username The username to validate
     * @param  string $password The password to validate
     * @return bool
     */
    protected function _perform_ldap_auth($username = '', $password = NULL)
    {
        if (empty($username))
        {
            log_message('debug', 'LDAP Auth: failure, empty username');
            return FALSE;
        }
        log_message('debug', 'LDAP Auth: Loading configuration');
        $this->config->load('ldap.php', TRUE);
        $ldap = [
            'timeout' => $this->config->item('timeout', 'ldap'),
            'host' => $this->config->item('server', 'ldap'),
            'port' => $this->config->item('port', 'ldap'),
            'rdn' => $this->config->item('binduser', 'ldap'),
            'pass' => $this->config->item('bindpw', 'ldap'),
            'basedn' => $this->config->item('basedn', 'ldap'),
        ];
        log_message('debug', 'LDAP Auth: Connect to ' . (isset($ldaphost) ? $ldaphost : '[ldap not configured]'));
        // Connect to the ldap server
        $ldapconn = ldap_connect($ldap['host'], $ldap['port']);
        if ($ldapconn)
        {
            log_message('debug', 'Setting timeout to ' . $ldap['timeout'] . ' seconds');
            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, $ldap['timeout']);
            log_message('debug', 'LDAP Auth: Binding to ' . $ldap['host'] . ' with dn ' . $ldap['rdn']);
            // Binding to the ldap server
            $ldapbind = ldap_bind($ldapconn, $ldap['rdn'], $ldap['pass']);
            // Verify the binding
            if ($ldapbind === FALSE)
            {
                log_message('error', 'LDAP Auth: bind was unsuccessful');
                return FALSE;
            }
            log_message('debug', 'LDAP Auth: bind successful');
        }
        // Search for user
        if (($res_id = ldap_search($ldapconn, $ldap['basedn'], "uid=$username")) === FALSE)
        {
            log_message('error', 'LDAP Auth: User ' . $username . ' not found in search');
            return FALSE;
        }
        if (ldap_count_entries($ldapconn, $res_id) !== 1)
        {
            log_message('error', 'LDAP Auth: Failure, username ' . $username . 'found more than once');
            return FALSE;
        }
        if (($entry_id = ldap_first_entry($ldapconn, $res_id)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, entry of search result could not be fetched');
            return FALSE;
        }
        if (($user_dn = ldap_get_dn($ldapconn, $entry_id)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, user-dn could not be fetched');
            return FALSE;
        }
        // User found, could not authenticate as user
        if (($link_id = ldap_bind($ldapconn, $user_dn, $password)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, username/password did not match: ' . $user_dn);
            return FALSE;
        }
        log_message('debug', 'LDAP Auth: Success ' . $user_dn . ' authenticated successfully');
        $this->_user_ldap_dn = $user_dn;
        ldap_close($ldapconn);
        return TRUE;
    }

    /**
     * Perform Library Authentication - Override this function to change the way the library is called
     *
     * @access protected
     * @param  string $username The username to validate
     * @param  string $password The password to validate
     * @return bool
     */
    protected function _perform_library_auth($username = '', $password = NULL)
    {
        if (empty($username))
        {
            log_message('error', 'Library Auth: Failure, empty username');
            return FALSE;
        }
        $auth_library_class = strtolower($this->config->item('auth_library_class'));
        $auth_library_function = strtolower($this->config->item('auth_library_function'));
        if (empty($auth_library_class))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_class');
            return FALSE;
        }
        if (empty($auth_library_function))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_function');
            return FALSE;
        }
        if (is_callable([$auth_library_class, $auth_library_function]) === FALSE)
        {
            $this->load->library($auth_library_class);
        }
        return $this->{$auth_library_class}->$auth_library_function($username, $password);
    }

    /**
     * check format.
     * @return [type] [description]
     */
    private function _checkSupportFormats()
    {
        $supported_formats = $this->_conf['system']['rest_supported_formats'];
        $supported_formats = !$supported_formats ? []: $supported_formats;
        $supported_formats = !is_array($supported_formats) ?  [$supported_formats] : $supported_formats;

        $default_format = $this->_conf['system']['rest_default_format'];
        if (!in_array($default_format, $supported_formats)) {
            $supportedFormats[] = $defaultFormat;
        }

        $this->_supported_formats = array_intersect_key($this->_supported_formats, array_flip($supported_formats));
    }

    /**
     * check ip black list.
     * @return [type] [description]
     */
    private function _checkIpBlackList()
    {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', \Helper\String::getRemoteAddr());
        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if (preg_match($pattern, $this->_conf['system']['rest_ip_blacklist']))
        {
            // Display an error response
            $this->response([
                $this->_conf['system']['rest_status_field_name'] => FALSE,
                $this->_conf['system']['rest_message_field_name'] => $this->_conf['lang']['text_rest_ip_denied']
            ], \Helper\Http::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * check method allowed.
     * @return [type] [description]
     */
    private function _checkMethodAllowed()
    {
        if (!in_array($this->request->method, $this->allowed_http_methods)) {
            $this->response([
                $this->_conf['system']['rest_status_field_name'] => FALSE,
                $this->_conf['system']['rest_message_field_name'] => $this->_conf['lang']['text_rest_notallowed_method']
            ], \Helper\Http::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    /**
     * Gets the default format from the configuration. Fallbacks to 'json'.
     * if the corresponding configuration option $config['rest_default_format']
     * is missing or is empty.
     *
     * @access protected
     * @return string The default supported input format
     */
    protected function _get_default_output_format()
    {
        $default_format = (string) $this->_conf['system']['rest_default_format'];
        return $default_format === '' ? 'json' : $default_format;
    }

    /**
     * Extend this function to apply additional checking early on in the process
     *
     * @access protected
     * @return void
     */
    protected function early_checks()
    {
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called
     *
     * @access protected
     * @return bool
     */
    protected function _auth_override_check()
    {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = isset($this->_conf['system']['auth_override_class_method']) ? $this->_conf['system']['auth_override_class_method'] : array();
        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method))
        {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method[$this->router->class]['*'])) // Check for class overrides
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class]['*'] === 'none')
                {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class]['*'] === 'basic')
                {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class]['*'] === 'digest')
                {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class]['*'] === 'session')
                {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class]['*'] === 'whitelist')
                {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
            // Check to see if there's an override value set for the current class/method being called
            if (!empty($auth_override_class_method[$this->router->class][$this->router->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none')
                {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'basic')
                {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'digest')
                {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'session')
                {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
        }
        // Assign the class/method/HTTP-method auth type override array from the config
        $auth_override_class_method_http = isset($this->_conf['system']['auth_override_class_method_http']) ? $this->_conf['system']['auth_override_class_method_http'] : array();
        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method_http))
        {
            // check for wildcard flag for rules for classes
            if(!empty($auth_override_class_method_http[$this->router->class]['*'][$this->request->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'none')
                {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'basic')
                {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'digest')
                {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'session')
                {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
            // Check to see if there's an override value set for the current class/method/HTTP-method being called
            if(!empty($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'none')
                {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'basic')
                {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'digest')
                {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'session')
                {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Deconstructor
     *
     * @author Chris Kacerguis
     * @access public
     * @return void
     */
    public function __destruct()
    {
    }


}
