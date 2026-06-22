<?php

namespace App\Services;

/**
 * RouterOS API Class v1.6
 * Lightweight, socket-based connection to MikroTik RouterOS API.
 */
class RouterosAPI
{
    public $debug = false; // set to true to print debug info
    public $error_no = 0;
    public $error_str = '';
    public $attempts = 1;
    public $delay = 2;
    public $timeout = 3;
    
    private $socket;
    private $connected = false;

    /**
     * Connect to RouterOS
     *
     * @param string  $host
     * @param string  $username
     * @param string  $password
     * @param integer $port
     * @return boolean
     */
    public function connect(string $host, string $username, string $password, int $port = 8728): bool
    {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $this->connected = false;
            $this->socket = @fsockopen($host, $port, $this->error_no, $this->error_str, $this->timeout);
            
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                
                // 1. Coba Login Metode Baru (RouterOS v7 & v6.43+)
                $this->write('/login', false);
                $this->write('=name=' . $username, false);
                $this->write('=password=' . $password);
                
                $response = $this->read(false);
                
                if (isset($response[0]) && $response[0] == '!done') {
                    $this->connected = true;
                    break;
                }
                
                // 2. Fallback ke Metode Lama (RouterOS v6.42 kebawah) jika metode baru di-trap
                if (isset($response[0]) && $response[0] == '!trap') {
                    fclose($this->socket);
                    $this->socket = @fsockopen($host, $port, $this->error_no, $this->error_str, $this->timeout);
                    
                    if ($this->socket) {
                        socket_set_timeout($this->socket, $this->timeout);
                        $this->write('/login');
                        $response2 = $this->read(false);
                        
                        if (isset($response2[0]) && $response2[0] == '!done') {
                            $matches = [];
                            if (isset($response2[1]) && preg_match('/=ret=([a-f0-9]+)/', $response2[1], $matches)) {
                                $chap = hex2bin($matches[1]);
                                $hash = md5("\x00" . $password . $chap);
                                
                                $this->write('/login', false);
                                $this->write('=name=' . $username, false);
                                $this->write('=response=00' . $hash);
                                
                                $login_result = $this->read(false);
                                if (isset($login_result[0]) && $login_result[0] == '!done') {
                                    $this->connected = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                fclose($this->socket);
            }
            
            if ($attempt < $this->attempts) {
                sleep($this->delay);
            }
        }
        
        if ($this->connected) {
            if ($this->debug) {
                echo "Connection opened successfully\n";
            }
            return true;
        } else {
            if ($this->debug) {
                echo "Connection failed: " . $this->error_str . " (" . $this->error_no . ")\n";
            }
            return false;
        }
    }

    /**
     * Disconnect from RouterOS
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
        $this->connected = false;
    }

    /**
     * Send command and return parsed result
     *
     * @param string $com
     * @param array  $arr
     * @return array
     */
    public function comm(string $com, array $arr = []): array
    {
        if (!$this->connected) {
            return [];
        }
        
        $count = count($arr);
        $this->write($com, ($count == 0));
        
        $i = 0;
        foreach ($arr as $key => $value) {
            $i++;
            if (is_numeric($key)) {
                $this->write($value, ($i == $count));
            } else {
                $paramKey = str_starts_with($key, '=') ? $key : '=' . $key;
                $this->write($paramKey . '=' . $value, ($i == $count));
            }
        }
        
        return $this->read();
    }

    /**
     * Write word to RouterOS
     *
     * @param string  $word
     * @param boolean $last
     */
    private function write(string $word, bool $last = true): void
    {
        $this->sendLength(strlen($word));
        fwrite($this->socket, $word, strlen($word));
        
        if ($last) {
            fwrite($this->socket, chr(0));
        }
        
        if ($this->debug) {
            echo ">>> " . $word . "\n";
        }
    }

    /**
     * Read words from RouterOS
     *
     * @param boolean $parse
     * @return array
     */
    private function read(bool $parse = true): array
    {
        $response = [];
        $line = '';
        $attrs = [];
        
        while (true) {
            $length = $this->getLength();
            if ($length > 0) {
                $word = '';
                $received = 0;
                
                while ($received < $length) {
                    $buffer = fread($this->socket, $length - $received);
                    if ($buffer === false || $buffer === '') {
                        break 2; // Connection broken
                    }
                    $word .= $buffer;
                    $received += strlen($buffer);
                }
                
                if ($this->debug) {
                    echo "<<< " . $word . "\n";
                }
                
                if (strpos($word, '!') === 0) {
                    $line = $word;
                } else {
                    $attrs[] = $word;
                }
            } else {
                if ($line == '!fatal') {
                    $this->disconnect();
                    break;
                }
                
                if ($parse) {
                    $item = [];
                    foreach ($attrs as $attr) {
                        if (preg_match('/^=([^=]+)=(.*)/s', $attr, $matches)) {
                            $item[$matches[1]] = $matches[2];
                        }
                    }
                    
                    if ($line == '!re') {
                        $response[] = $item;
                    } elseif ($line == '!done') {
                        if (!empty($item)) {
                            $response[] = $item;
                        }
                        break;
                    } elseif ($line == '!trap') {
                        $response['trap'] = $item;
                    }
                    
                    $attrs = [];
                } else {
                    $response[] = $line;
                    foreach ($attrs as $attr) {
                        $response[] = $attr;
                    }
                    break;
                }
            }
        }
        
        return $response;
    }

    /**
     * Send word length
     *
     * @param integer $length
     */
    private function sendLength(int $length): void
    {
        if ($length < 0x80) {
            fwrite($this->socket, chr($length));
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            fwrite($this->socket, chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            fwrite($this->socket, chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            fwrite($this->socket, chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } else {
            fwrite($this->socket, chr(0xF0) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        }
    }

    /**
     * Get word length
     *
     * @return integer
     */
    private function getLength(): int
    {
        $char = fread($this->socket, 1);
        if ($char === false || $char === '') {
            return 0;
        }
        
        $byte = ord($char);
        if (($byte & 0x80) == 0x00) {
            return $byte;
        } elseif (($byte & 0xC0) == 0x80) {
            $char2 = fread($this->socket, 1);
            $byte2 = ord($char2);
            return (($byte & 0x3F) << 8) + $byte2;
        } elseif (($byte & 0xE0) == 0xC0) {
            $char2 = fread($this->socket, 2);
            return (($byte & 0x1F) << 16) + (ord($char2[0]) << 8) + ord($char2[1]);
        } elseif (($byte & 0xF0) == 0xE0) {
            $char2 = fread($this->socket, 3);
            return (($byte & 0x0F) << 24) + (ord($char2[0]) << 16) + (ord($char2[1]) << 8) + ord($char2[2]);
        } elseif (($byte & 0xF8) == 0xF0) {
            $char2 = fread($this->socket, 4);
            return (ord($char2[0]) << 24) + (ord($char2[1]) << 16) + (ord($char2[2]) << 8) + ord($char2[3]);
        }
        
        return 0;
    }
}
