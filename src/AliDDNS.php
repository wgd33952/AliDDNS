<?php
namespace wgd33952\AliyunCloud;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Alidns\Alidns;

class AliDDNS {

    private static $instance;

    private $config = [];

    /**
     * Init the config.
     * Example:
     *    [
     *        "accessKeyId" => "accessKeyId",
     *        "accessKeySecret" => "accessKeySecret",
     *        "domain" => "domain.com",
     *        "rr" => "www",
     *        "ttl" => 600
     *   ]
     *
     * @param array $config
     * @return AliDDNS
     */
    public static function init($config = []): AliDDNS
    {
        self::$instance = new self();
        self::$instance->config = $config;
        return self::$instance;
    }

    /**
     * Resolve the domain name to the current host Internet IP.
     *
     * @return bool
     */
    public function run(): bool
    {
        try {
            AlibabaCloud::accessKeyClient($this->config['accessKeyId'], $this->config['accessKeySecret'])
                ->regionId('cn-shenzhen')
                ->asDefaultClient();
        } catch (ClientException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        try {
            $request = Alidns::v20150109()->describeDomainRecords();
            $result = $request
                ->withDomainName($this->config['domain'])
                ->debug(false) // Enable the debug will output detailed information
                ->connectTimeout(3) // Throw an exception when Connection timeout
                ->timeout(5) // Throw an exception when timeout
                ->request();
            $this->log('--------- START ---------');
            $this->log('Exist Domains:');
            print_r($result->toArray());
            $result_data = $result->toArray();
            if (isset($result_data['DomainRecords']) && !empty($result_data['DomainRecords']['Record'])) {
                $recordId = '';
                foreach ($result_data['DomainRecords']['Record'] as $record) {
                    if ($record['RR'] == $this->config['rr']) {
                        if ($record['value'] != self::getIP()) {
                            $recordId = $record['RecordId'];
                        } else {
                            $this->log("It's no need to change.");
                        }
                        break;
                    }
                }
                if ($recordId) { // record exist, edit it.
                    $this->log('Changing...');
                    $change_result = AlibabaCloud::rpc()
                        ->product('Alidns')
                        ->version('2015-01-09')
                        ->action('UpdateDomainRecord')
                        ->method('POST')
                        ->host('alidns.cn-shenzhen.aliyuncs.com')
                        ->options([
                            'query' => [
                                'RecordId' => $recordId,
                                'RR' => $this->config['rr'],
                                'Type' => 'A',
                                'Value' => self::getIP(),
                                'TTL' => $this->config['ttl'],
                            ],
                        ])
                        ->request();
                    $this->log('Change Result:');
                    $this->log($change_result->toArray());
                    $this->log('Change Success!!');
                } else {  // record not exist, create it.
                    $this->log('Creating...');
                    $request = Alidns::v20150109()->addDomainRecord();
                    $create_result = $request
                        ->debug(false) // Enable the debug will output detailed information
                        ->withDomainName($this->config['domain'])
                        ->withRR($this->config['rr'])
                        ->withType('A')
                        ->withValue(self::getIP())
                        ->withTTL($this->config['ttl'])
                        ->connectTimeout(3) // Throw an exception when Connection timeout
                        ->timeout(5) // Throw an exception when timeout
                        ->request();
                    $this->log('Create Result:');
                    $this->log($create_result->toArray());
                    $this->log('Creating Success!!');
                }
                $this->log('--------- END ---------');
                return true;
            } else {
                $this->log('Config error!!');
                $this->log('--------- END ---------');
                return false;
            }
        } catch (ClientException $exception) {
            echo $exception->getMessage() . PHP_EOL;
            return false;
        } catch (ServerException $exception) {
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getErrorCode() . PHP_EOL;
            echo $exception->getRequestId() . PHP_EOL;
            echo $exception->getErrorMessage() . PHP_EOL;
            return false;
        }
    }

    /**
     * get the external Ip
     * @return mixed
     */
    public static function getIP()
    {
        $externalContent = file_get_contents('http://checkip.dyndns.com/');
        preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
        return $m[1];
    }

    private function log($msg)
    {
        if ($this->config['debug']) {
            if (is_array($msg)) {
                print_r($msg);
            } else {
                echo $msg . PHP_EOL;
            }
        }
    }
}