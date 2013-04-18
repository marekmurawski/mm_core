<?php

/* Security measure */
if ( !defined('IN_CMS') )
    exit();


class mmCore {

    const VIEW_FOLDER = "../../plugins/mm_core/views/";
    const GLUE        = '<br/>';

    public static $messages = array( );

    public static function callback_mm_core_stylesheet() {
        echo '<link rel="stylesheet" href="' . PLUGINS_URI . 'mm_core/css/mm_core.css" />';

    }


    /**
     *
     * @param type $message
     * @param type $status
     * @param type $arr
     */
    public static function respond($message = '', $status = 'OK', $arr = array( )) {
        // set messages
        $msg_text = (count(self::$messages) > 0) ? implode(self::GLUE, self::$messages) . self::GLUE . $message : $message;
        $default  = array(
                    'message'  => $msg_text,
                    'exe_time' => execution_time(),
                    'mem_used' => memory_usage(),
                    'status'   => $status,
        );

        // add any additional fields
        $response = array_merge($default, $arr);

        echo json_encode($response);
        if ( $status !== 'OK' )
            header("HTTP/1.0 404 Not found");
        die();

    }


    public static function success($message, $arr = array( )) {
        self::respond($message, 'OK', $arr);

    }


    public static function appendResult($message) {
        self::$messages[] = $message;

    }


    public static function failure($message, $arr = array( )) {
        self::respond($message, 'error', $arr);

    }


    /**
     * HELPER FUNCTIONS
     */

    /**
     * Parse values in multiline string like
     *
     * name1 => value1
     * name2 => value2
     *
     * into Array
     *
     * @param String $values
     * @param String $delimeter
     * @param String $comment
     * @return Array
     */
    public static function parseValues($values, $delimeter = '=>', $comment = '#') {
        $result = array( );
        foreach ( explode(PHP_EOL, $values) as $value ) {
            $value = trim($value);
            if ( !startsWith($value, $comment) && (!empty($value)) ) {
                $pos_delimeter = strpos($value, $delimeter);
                if ( $pos_delimeter > 1 ) {
                    $key          = trim(substr($value, 0, $pos_delimeter));
                    $val          = trim(substr($value, $pos_delimeter + strlen($delimeter)));
                    $val          = strlen($val) > 0 ? $val : $key;
                    $result[$key] = $val;
                } else {
                    $key          = trim($value);
                    $result[$key] = $key;
                }
            }
        };
        return $result;

    }


}