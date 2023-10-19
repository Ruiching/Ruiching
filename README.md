### 项目依赖
1、PHP >= 7.4.3

2、Mysql >= 5.7

### 部署步骤
1、将项目根目录下文件 .env.production 改名为 .env，修改其中的数据库配置

2、将项目根目录下的composer.phar迁移到系统命令文件中，参考命令：mv composer.phar /usr/local/bin/composer,全局替换composer的扩展源：composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

3、在项目根目录执行命令：composer update

4、在项目根目录依次执行命令：php think migrate:run 和 php think seed:run

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

2、创建容器：docker run -d -p 8282:80, 9000:9000 --name science_tree science_tree:1.0

docker run -d -p 9000:9000 --name science_tree_nginx science_tree:1.0

3、查看容器：docker ps -a

4、进入容器：docker exec -it science_tree /bin/bash

5、删除容器：docker rm -f science_tree

####问题：

1、出现端口占用情况
```
通过命名：lsof -i:端口号 查看占用情况
```