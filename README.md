# AliDDNS
通过阿里云提供的DDNS，动态将域名解析到本机公网IP

### 1. 安装Composer
armbain系统：`apt install composer`

centos：`yum install composer`

其它系统，请参照composer指南进行安装[https://pkg.xyz/#how-to-install-composer]。

### 2. 从源码库拉取代码，并拉取包：
```shell
$ git clone wgd33952/aliddns
$ composer update
```

### 3. 获取AccessKey ID和AccessKey Secret值

- 登录阿里云控制台后台
- 右上角头像处选择点击“AccessKey管理”页面
- 创建AccessKey，并获得AccessKey ID和AccessKey Secret的值

### 4. 配置需要解析的域名参数
根据实际情况，更改example/demo.php中对应的配置参数
```php
$config = [
    // 开启调试模式
    "debug" => true,
    // 阿里云 AccessKey ID
    "accessKeyId" => "accessKeyId",
    // 阿里云 AccessKey Secret
    "accessKeySecret" => "accessKeySecret",
    // 域名
    "domain" => "domain.com",
    // 主机记录
    "rr" => "www",
    // TTL
    "ttl" => 600
];
```

### 5. 运行
```shell
$ php example/demo.php
```

### 6.定时更新域名解析
通过linux的crontab，可以定时调用更新程序，在不在家时也能自动更新解析，一劳永逸

编辑crontab文件：
```shell
$ crontab -e
```
输入任务内容并保存：
```shell
# 每5分钟检查本地IP变动情况，并更新解析
*/5 * * * * /usr/bin/php /root/AliDDNS/example/demo.php
```
重启crontab
```shell
// Armbain
$ service cron restart
// CentOS
$ systemctl restart cronb
```
