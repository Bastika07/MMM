<?php
/* Utility functions
 *
 * Hinzugefügt von Y0Gi, 13-Apr-2007.
 */

# ---------------------------------------------------------------- #
# HTTP header stuff

$http_status_codes = array(
    # Success
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',

    # Redirection
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => 'Switch Proxy',
    307 => 'Temporary Redirect',

    # Client Error
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Confict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Request Range Not Satisfiable',
    417 => 'Expectation Failed',

    # Server Error
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'Version Not Supported',
    );

/* Eine Fehlermeldung ausgeben und die Verarbeitung abbrechen. */
function abort($code=200, $msg='') {
    global $http_status_codes;
    header(sprintf('HTTP/1.0 %d %s', $code, $http_status_codes[$code]));
    echo $msg . "\n";
    exit();
}

/* Eine HTTP-Umleitung durchführen. */
function redirect_to($url) {
    header('Location: ' . $url);
    exit();
}

# ---------------------------------------------------------------- #
# framework/structure

# Smarty vorbereiten.
require_once('classes/SmartyAdmin.class.php');
$smarty = new SmartyAdmin();


/* Return the controller name (i.e. the name of the current script
 * without the file extension.
 */
function get_ctrl_name() {
    $basename = basename($_SERVER['SCRIPT_NAME']);
    return substr($basename, 0, strrpos($basename, '.'));
}

/* Die Action-Methode einer Controller-Instanz ausführen. */
function exec_ctrl() {
    $ctrl = new Controller();
    $action = $_GET['action'] ? $_GET['action'] : 'index';
    if (! method_exists($ctrl, $action)) {
        exit("Fehler: Die angeforderte Aktion '$action' existiert nicht.");
    }

    # Namen des zu verwendenden Templates ermitteln.
    $template = sprintf(get_ctrl_name() . '_' . $action);

    # Action-Methode aufrufen.
    $vars = call_user_func_array(array(&$ctrl, $action), array());

    # Template füllen, rendern und Ergebnis ausgeben.
    global $smarty;
    $smarty->assign($vars);
    $smarty->displayWithFallback($template . '.tpl');
}

# ---------------------------------------------------------------- #
# JSON

/* Encode $data as JSON. */
function to_json($data) {
    require_once('json.php');
    $json = new Services_JSON();
    return $json->encode($data);
}

/* Decode JSON data. */
function from_json($json_data) {
    require_once('json.php');
    $json = new Services_JSON();
    return $json->decode($json_data);
}

/* Send $data as JSON to the client. */
function export_json($data) {
    header('Content-Type: text/javascript');
    echo to_json($data);
    exit;
}

/* Read JSON data sent via POST. */
function import_json() {
    return from_json($GLOBALS['HTTP_RAW_POST_DATA']);
}

# ---------------------------------------------------------------- #

