<?php

namespace Alibe\Geonames\Lib;

class Exec {
    protected $conn;
    protected $clid;

    public function __construct($clid,$conn) {
        $this->conn=$conn;
        $this->conn['baseHost']=rtrim($this->conn['baseHost'],'/');

        $this->clid=$clid;
    }

    public function get(array $par, $fCall='JSON') {
        $fCall=(false!==$fCall)?$fCall:'JSON';

        $lang='';
        if(isSet($this->conn['settings']['lang']) && $this->conn['settings']['lang']) {
            $lang=$this->conn['settings']['lang'];
        }
        if(isSet($par['lang']) && $par['lang']) {
            $lang=$par['lang'];
            unset($par['lang']);
        }
        if($lang!='') {
            $lang='&lang='.$lang;
        }

        unset($par['clID']);
        unset($par['username']);

        $url=$this->conn['baseHost'].'/'.
            $par['cmd'].$fCall.
            '?username='.$this->clid.$lang;

        if(isSet($par['query'])) {
            foreach ($par['query'] as $k => $v) {
                if(null==$v || false==$v) {
                    continue;
                }

                if(is_bool($v) && true===$v) {
                    $v='true';
                }


                $oprt='=';
                if(preg_match('/^EXCLUDE/',$k))  {
                    $oprt='!=';
                    $k=preg_replace('/^EXCLUDE/','',$k);
                }


                if(is_array($v)) {
                    foreach ($v as $ka=>$va) {
                        $url.='&'.$k.$oprt.$va;
                    }
                } else {
                    $url.='&'.$k.$oprt.$v;
                }
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        if(isSet($par['asIs']) && $par['asIs']) {
            return $response;
        }

        if(isSet($par['preOutput']) && $par['preOutput']) {
            $cmd=$par['preOutput'];
            $response=$this->$cmd($response);
        }


        return $this->output($response, $this->conn['settings']['format']);
    }

    protected function output($res,$format) {
        $format=mb_strtolower($format);
        switch($format) {
            case 'array':
                return (array) json_decode($res, true);
            break;

            case 'object':
                return (object) json_decode($res);
            break;

            default:
                throw new \Exception('Invalid format. Use "array" or "object"');
        }
    }

    protected function xmlConvert($res) {
        $xml = simplexml_load_string($res, null, LIBXML_NOCDATA);
        $ns = $xml->getNamespaces(true);
        $rit=$this->sxeIter($xml,$ns);

        return json_encode($rit);
    }

    protected function sxeNsParse($obj,$ns) {
        $r=array();
        foreach($ns as $kn=>$n) {
            $r[$kn]=$obj->children($ns[$kn]);
        }
        $r=array_merge($r,$this->sxeIter($obj,$ns));
        return array_filter($r);
    }

    protected function sxeIter($sxe,$ns) {
        $r=array();
        foreach($sxe as $k=>$v) {
            if($v->count()>1) {
                if(isset($sxe->$k[1])) {
                    for($j=0;$j<count($sxe->$k);$j++) {
                        $r[$k][$j]=$this->sxeNsParse($sxe->$k[$j],$ns);
                    }
                } else {
                    $r[$k]=$this->sxeNsParse($v,$ns);
                }
            } else {
                $r[$k]= (string) $v;
            }
        }
        return $r;
    }

}
