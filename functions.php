<?php

function parseMsg($msg) {
    $command = ['verb' => '', 'pref' => '', 'count' => 0, 'params' => []];
    if($msg) {
        $msg    = ltrim($msg);
        $prefix = substr($msg, 0, 1);
        if(in_array($prefix, ['!', '@', '/'])) {
            $command['pref']  = $prefix;
            if(strlen($msg) > 1) {
                $verb = strtolower(substr(rtrim($msg), 1, strpos($msg.' ', ' ') - 1));
                if(ctype_alnum($verb)) {
                    $tokens = explode(' ', trim($msg));
                    $command['verb']  = $verb;
                    $command['count'] = count($tokens) - 1;
                    for($i = 1; $i < count($tokens); $i++) {
                        $command['params'][$i - 1] = trim($tokens[$i]);
                    }
                }
            }
        }
    }
    return $command;
}



?>
