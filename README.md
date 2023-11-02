### 项目依赖
1、PHP >= 7.4.3

2、Mysql >= 5.7

### 服务器部署

1、将 ./deploy/www.conf，放入 /etc/php-fpm.d/（PHP-FPM配置） 目录下，重启PHP-FPM服务

2、将 ./deploy/web.conf，放入 /etc/nginx/conf.d/（nginx站点生效） 目录下，重启nginx服务

3、需确保 nginx 和 php-fpm 服务的用户组均为 nginx；相关用户组配置文件如下：

```
/etc/php-fpm.d/www.conf
user = nginx
group = nginx

/etc/nginx/nginx.conf
user nginx;
```

### 部署步骤（弃用）
1、将项目根目录下文件 .env.production 改名为 .env，修改其中的数据库配置

2、将项目根目录下的composer.phar迁移到系统命令文件中，参考命令：mv composer.phar /usr/local/bin/composer,全局替换composer的扩展源：composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

3、在项目根目录执行命令：composer update

5、项目运行目录为根目录下的public文件夹

6、nginx需要配置伪静态，参考如下：
```
location / {
    if (!-e $request_filename) {
        rewrite  ^(.*)$  /index.php?s=/$1  last;
        break;
    }
}
```

###Docker操作
1、创建镜像文件：docker build -t science_tree:1.0 .

2、镜像打包：docker save science_tree:1.0 > science_tree.tar

3、镜像解压：docker load -i science_tree.tar

4、创建容器：docker run -d -p 8282:8899 --name science_tree science_tree:1.0

5、查看容器：docker ps -a

6、进入容器：docker exec -it science_tree /bin/bash

7、删除容器：docker rm -f science_tree

8、进入容器修改配置文件：/var/www/science_tree/.env
```
APP_DEBUG = false  #是否开启调试模式

[APP] #应用配置
USER_LOGIN_TOKEN = 4gVYC0mFjG4xr16osbz5jSXQQxPJbCJr #账号标识符，32位字符串
USER_LOGIN_NAME = admin #登录账号
USER_LOGIN_PASSWORD = admin123 #登录密码
PROJECT_NAME = 腾讯科技树 #项目名
DEFAULT_TIMEZONE = Asia/Shanghai #默认时区
DEFAULT_LANG = zh-cn #默认语言
TOKEN_KEY=EQD97HePTgaNa0r4p0kS4RIV2T6GGV7P #登录加密key
TOKEN_TIME=86400 #登录TOKEN过期时间
DELAY_TIME=300 #登录TOKEN延迟时间

[DATABASE] #数据库配置
TYPE = mysql #数据库类型
HOSTNAME = 127.0.0.1 #数据库地址
DATABASE = science_tree #数据库名
USERNAME = root #数据库账号
PASSWORD = 123456 #数据库密码
HOSTPORT = 3306 #数据库端口
CHARSET = utf8 #数据库字符编码
DEBUG = false

[CACHE] #缓存引擎，默认为文件类型
DRIVER = file

[REDIS] #Redis配置
HOST = 127.0.0.1
PORT = 6379
PASSWORD =

```

####问题：

1、出现端口占用情况
```
通过命名：lsof -i:端口号 查看占用情况
```