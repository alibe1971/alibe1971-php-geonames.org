<?php

namespace Alibe\PhpGeonamesorg\Lib;

class Exec {
    protected $conn;

    public function __construct($conn) {
        $this->conn=$conn;
        $this->conn['baseHost']=rtrim($this->conn['baseHost'],'/');
        $this->conn['cmdSuffix']=$this->getSuffix(mb_strtolower($conn['connection']['format']));
    }

    public function get(array $par) {
        $url=$this->conn['baseHost'].'/'.
            $par['cmd'].$this->conn['cmdSuffix'].
            '?username='.$this->conn['connection']['clID'].
            '&lang='.$this->conn['connection']['lang'];

        if(isSet($par['query'])) {
            foreach ($par['query'] as $k => $v) {
                $url.='&'.$k.'='.$v;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        if($par['final']) {
            return $this->output($response, $this->conn['connection']['format']);
        }
        return json_decode($response);
    }

    protected function output($res,$format) {
        if(!is_array($res)) {
            switch($format) {
                case 'array':
                    return json_decode($res);
                break;
                default:
                    return $res;
            }
        }

        switch($format) {
            case 'array':
                return $res;
            break;

            case 'json':
                return json_encode($res);
            break;

            default:
                return $response;
        }

    }

    protected function getSuffix($format) {
        $suffix=[
            'array'=>'JSON',
            'json'=>'JSON',
            'xml'=>'',
            'csv'=>'CSV'
        ];
        if(isSet($suffix[$format])) {
            return $suffix[$format];
        }
    }
}
