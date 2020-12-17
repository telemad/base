<?php
mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");
//$v = mb_split('、',"日、に、本、ほん、語、ご");

class Command {

    private $prefixes;
    public  $optPrefix;

    public $msgChars;
    public $verb;
    public $prefix;
    public $paramStart;

    public function  __construct(string $prefixes, bool $optPrefix = false) {
        $this->prefixes  = str_split($prefixes);
        $this->optPrefix = in_array(' ', $this->prefixes);
        $this->prefixes  = str_replace(' ', '', $this->prefixes);
    }

    public function parse(array $update): void {
        $msgOrig = $update['message']['message']?? null;

        $this->prefix     = '';
        $this->verb       = '';
        $this->paramStart =  0;

        if($msgOrig) {
            $this->msgChars = str_split($msgOrig);
            $msglen = count($this->msgChars);

            $index  = 0;
            for(; $index < $msglen && $this->msgChars[$index] === ' '; $index++);

            if(in_array($this->msgChars[$index], $this->prefixes)) {
                $this->prefix = $this->msgChars[$index++];

                for(; $index < $msglen && $this->msgChars[$index] !== ' ';) {
                    $this->verb .= $this->msgChars[$index++];
                }
                $this->verb = strtolower($this->verb);
                $this->paramStart = $index++;

                if(!ctype_alnum($this->verb)) {
                    $this->verb = '';
                }
            }
        }
    }

    public function advance(): ?string {
        $msglen = count($this->msgChars);

        $index = $this->paramStart;
        for(; $index < $msglen && $this->msgChars[$index] === ' '; $index++);
        $this->paramStart = $index;

        $param = '';
        for(; $index < $msglen && $this->msgChars[$index] !== ' ';) {
            $param .= $this->msgChars[$index++];
        }

        $this->paramStart = $index++;
        return $param !== ''? $param : null;
    }

    public function getCommand() {
        $command = [
            'prefix' => $this->prefix,
            'verb'   => $this->verb,
            'index'  => $this->paramStart,
        ];
        return $command;
    }

    public function in(string $verb): bool {
        return $this->verb === strtolower(trim($verb));
    }

    public function paramNone(): bool {
        $param = $this->advance();
        return $param === null;
    }

    public function paramRemaining(): string {
        $msglen = count($this->msgChars);
        $index  = $this->paramStart;
        $param  = '';
        for(; $index < $msglen;) {
            $param .= $this->msgChars[$index++];
        }
        $this->paramStart = $index;
        return $param;
    }

    public function paramString(bool $moreParam = false): ?string {
        return $this->advance();
    }

    public function paramSwitch(bool $moreParam = false): ?string {
        $param = strtolower($this->advance());
        return $param === 'on' || $param === 'off' ? $param : null;
    }

    public function paramInt(bool $moreParam = false): ?int {
        $param = $this->advance();
        return (ctype_digit($param))? intval($param) : null;
    }

    public function paramUrls(bool $moreParam = false): ?array {
        // TBD
        return null;
    }
}




class Command_OLD {

    private array $prefixes;

    public string $msg;
    public string $verb;
    public string $pref;
    public int    $count;
    public array  $params;

    public function  __construct(string $prefixes) {
        $this->prefixes = str_split($prefixes);
    }

    public function parse(array $update): void {
        $msg = strtolower(trim($update['message']['message']?? ''));
        $this->verb   = '';
        $this->pref   = '';
        $this->count  = 0;
        $this->params = [];
        $prefix = $msg[0];
        if(in_array($prefix, $this->prefixes)) {
            $this->prefix  = $prefix;
            if(strlen($msg) > 1) {
                $verb = strtolower(substr(rtrim($msg), 1, strpos($msg.' ', ' ') - 1));
                if(ctype_alnum($verb)) {
                    $tokens = explode(' ', trim($msg));
                    $this->verb  = $verb;
                    $this->count = count($tokens) - 1;
                    for($i = 1; $i < count($tokens); $i++) {
                        $this->params[$i - 1] = trim($tokens[$i]);
                    }
                }
            }
        }
    }

    public function getCommand() {
        $command = [
            'verb'   => $this->verb,
            'pref'   => $this->pref,
            'count'  => $this->count,
            'params' => $this->params
        ];
        return $command;
    }

    public function in(string ... $verbs): bool {
        foreach ($verbs as $verb) {
            if ($this->command['verb'] === $verb) {
                echo($verb. ' ' . $this->command['count'].PHP_EOL);
                return true;
            }
        }
        return false;
    }

    public function cnt(int $paramCount) {
        $same = $this->command['count'] === $paramCount;
        if(!$same) {
            echo('WRONG!'.PHP_EOL);
        }
        return $same;
    }

    public function first(): string {
        return $this->command['params'][0];
    }
}

/*
    $cnt = function(int $paramCount) use($command): bool {
        $same = $command['count'] === $paramCount;
        if(!$same) {
            echo('WRONG!'.PHP_EOL);
        }
        return $same;
    };
    $in = function(string ... $verbs) use($command): bool {
        foreach ($verbs as $verb) {
            if ($command['verb'] === $verb) {
                echo($verb. ' ' . $command['count'].PHP_EOL);
                return true;
            }
        }
        return false;
    };
    $frstStr = function() use($command): string {
        return $command['params'][0];
    };
    $frstInt = function() use($command): int {
        return intval($command['params'][0]);
    };
    $vrb = $command['verb'];
    $mp  = $this;
    $bad = function() use($mp, $vrb, $peer) {
        yield $mp->messages->sendMessage([
            'peer'    => $peer,
            'message' => 'Invalid ' . $vrb . ' arguments'
        ]);
    };
    */