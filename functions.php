<?php

function toJSON($var, bool $oneLine = false): string {
    $opts = JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES;
    $json = \json_encode($var, $opts | (!$oneLine? JSON_PRETTY_PRINT : 0));
    $json = ($json !== '')? $json : var_export($var, true);
    return $json;
}

function peer(array $peer): array {
    $peer = $peer['message']['to_id']?? $peer;
    $peer = $peer['to_id']?? $peer;
    if(isset($peer['_'])) {
        switch ($peer['_']) {
            case 'peerUser':    return ['_' => 'user',    'id' => $peer['user_id']];
            case 'peerChat':    return ['_' => 'chat',    'id' => $peer['chat_id']];
            case 'peerChannel': return ['_' => 'channel', 'id' => $peer['channel_id']];
        }
    }
    return null;
}

function updSummary(array $update, array $peerInfo = null): string {
    $peerType  = $update['message']['to_id']['_'];
    $peerID    =  $peerType === 'peerUser'   ? $update['message']['to_id']['user_id'] :
                 ($peerType === 'peerChat'   ? $update['message']['to_id']['chat_id'] :
                 ($peerType === 'peerChannel'? ($update['message']['to_id']['channel_id']): 0));
    $peerCli   =  $peerType .'#'. strval($peerID);
    $peerTitle = '';
    $userID    = $update['message']['from_id']?? null;
    $msgID     = $update['message']['id'];
    $msg       = $update['message']['message'] ?? '';

    $msgFront   = substr(str_replace(array("\r", "\n"), '<br>', $msg), 0, 50);
    $updSummary = $update['_'] . '/' . $update['pts'] . '  ' .
                  ($userID?'from:' . $userID . '   ' : '') .
                  'to:'. $peerCli . ($peerTitle? '[' . $peerTitle . ']  ' : '  ') .
                  'msg'.$msgID . ':[' . $msgFront . ']';
    return $updSummary;
}


function parseCommand(string $msg, string $prefixes = '!/', int $maxParams = 3): array
{
    $command = ['verb' => '', 'prefix' => '', 'params' => []];
    $msg = trim($msg);
    if($msg && strlen($msg) >= 2 && strpos($prefixes, $msg[0]) !== false) {
        $verb = strtolower(substr(rtrim($msg), 1, strpos($msg.' ', ' ') - 1));
        if(ctype_alnum($verb)) {
            $command['prefix'] = $msg[0];
            $command['verb']   = $verb;
            $tokens = explode(' ', $msg, $maxParams + 1);
            for($i = 1; $i < count($tokens); $i++) {
                $command['params'][$i - 1] = trim($tokens[$i]);
            }
        }
    }
    return $command;
}
