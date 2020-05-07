<?php

namespace Cmmia\Encrypt;

class Ftp
{
    private $connection;
    private $sftp;

    public function __construct($host, $port = 22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (!$this->connection) {
            throw new Exception("Could not connect to $host on port $port.");
        }
    }

    public function login($username, $password)
    {
        if (!@ssh2_auth_password($this->connection, $username, $password)) {
            throw new Exception("Could not authenticate with username $username "."and password $password.");
        }
        $this->sftp = @ssh2_sftp($this->connection);
        if (!$this->sftp) {
            throw new Exception('Could not initialize SFTP subsystem.');
        }
    }

    public function uploadFile($local_file, $remote_file)
    {
        $sftp = $this->sftp;
        $pathinfo=pathinfo($remote_file);
        if (!file_exists("ssh2.sftp://" . intval($sftp).$pathinfo['dirname'])) {
            @ssh2_sftp_mkdir($this->sftp,$pathinfo['dirname'],0777,true);
        }

        $stream = @fopen("ssh2.sftp://" . intval($sftp) . $remote_file, 'w');
        

        if (!$stream) {
            throw new Exception("Could not open file: $remote_file");
        }
        $data_to_send = @file_get_contents($local_file);
        if (false === $data_to_send) {
            throw new Exception("Could not open local file: $local_file.");
            return false;
        }
        if (false === @fwrite($stream, $data_to_send)) {
            throw new Exception("Could not send data from file: $local_file.");
            return false;
        }
        @fclose($stream);
    }
}


